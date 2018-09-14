<?php
namespace Qbus\SubsiteGenerator\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * SubsiteGeneratorService
 *
 * @author Benjamin Franzke <bfr@qbus.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SubsiteGeneratorService
{
    /**
     * $data passed to the DataHandler
     *
     * @var array
     */
    protected $data = [];

    /**
     * Storage for temporary new-ids
     *
     * @var array
     */
    protected $pool = [];

    /**
     * @param  string $title
     * @param  string $subodmain
     * @param  string $uAccount
     * @param  string $uPassword
     * @param  string $uName
     * @param  string $uMail
     * @return bool
     */
    public function create(
        $title,
        $subdomain,
        $uAccount,
        $uPassword,
        $uName,
        $uMail
    ) {
        $config = $this->getConfig();
        $db = $this->getDatabaseConnection();

        $templateId   = $config->get('template_page_uid');
        $destPid      = $config->get('target_pid');
        $baseGroupId  = $config->get('base_group_uid');
        $domainSuffix = $config->get('domain_suffix');
        $storageUid   = $config->get('file_storage_uid');

        $subdomain = trim(str_replace('/', '.', $subdomain), '.');
        $folderName = trim(str_replace('.', '/', $subdomain), '/');
        $urlPath = $folderName;

        $rootPageId = $this->cloneFromTemplate($templateId, $destPid);
        $db->exec_UPDATEquery(
            'pages',
            'uid=' . intval($rootPageId),
            ['title' => $title, 'hidden' => 0]
        );
        if (!$domainSuffix) {
            if (ExtensionManagementUtility::isLoaded('realurl')) {
                $db->exec_UPDATEquery(
                    'pages',
                    'uid=' . intval($rootPageId),
                    ['tx_realurl_pathsegment' => $urlPath, 'tx_realurl_pathoverride' => 1]
                );
            }
        } else {
            $db->exec_UPDATEquery(
                'sys_domain',
                'pid=' . intval($rootPageId),
                ['domainName' =>  $subdomain . $domainSuffix]
            );
        }

        $storageRepository = $this->getStorageRepository();
        $storage = $storageRepository->findByUid($storageUid);
        if ($storage === null) {
            throw new \RuntimeException(
                'Storage ' . $storageUid . ' not available. (can be configured in the extension manager)',
                1454058543
            );
        }

        $folder = $storage->createFolder($folderName);

        $filemount = $this->addFileMount(
            'Subsite ' . $title . '(' . $subdomain . ')',
            '/' . $folderName . '/',
            $storageUid
        );
        $begroup = $this->addBEGroup(
            'Subsite ' . $title . '(' . $subdomain . ')',
            [$filemount],
            [$baseGroupId],
            [$rootPageId],
            'options.defaultUploadFolder = ' . $storageUid . ':' . $folderName . '/'
        );
        $this->addBEUser(
            $uAccount,
            $uPassword,
            $uName,
            $uMail,
            [$begroup]
        );

        $tce = $this->createDataHandler($this->data);
        $tce->process_datamap();
        BackendUtility::setUpdateSignal('updatePageTree');

        $_params = [
            'config' => $config,
            'rootPageId' => $rootPageId,
            'domainSuffix' => $domainSuffix,
            'subdomain' => $subdomain,
            'urlPath' => $urlPath,
            'tce' => $tce,
            'folder' => $folder,
        ];
        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['subsite_generator']['postCreate'] ?? [] as $_funcRef) {
            if ($_funcRef) {
                GeneralUtility::callUserFunction($_funcRef, $_params, $this);
            }
        }

        return true;
    }

    /**
     * @param  int $templateId
     * @param  int $destPid
     * @return int $newRootId
     */
    protected function cloneFromTemplate($templateId, $destPid = 0)
    {
        $cmd = [];
        $cmd['pages'][$templateId]['copy'] = $destPid;

        $tce = $this->createDataHandler([], $cmd);
        $tce->copyTree = 99;
        $tce->process_cmdmap();

        return $tce->copyMappingArray_merged['pages'][$templateId];
    }

    /**
     * @param  string $title       title of the file mount
     * @param  string $path        path of the file mount
     * @param  int    $storage_uid uid for storage to use
     * @return string
     */
    protected function addFileMount($title, $path, $storage_uid = 1)
    {
        $id = $this->allocateId('filemount-' . $title);

        $this->data['sys_filemounts'][$id] = [
            'title' => $title,
            'path' => $path,
            'base' => $storage_uid,
            'pid' => 0,
        ];

        return $id;
    }

    /**
     * @param  string $title
     * @param  array  $file_mounts
     * @param  array  $groups
     * @param  array  $db_mounts
     * @param  string $tsconfig
     * @return string
     */
    protected function addBEGroup($title, $file_mounts = [], $groups = [], $db_mounts = [], $tsconfig = '')
    {
        $id = $this->allocateId('begroup' . $title);

        $this->data['be_groups'][$id] = [
            'title' => $title,
            'file_mountpoints' => implode(',', $file_mounts),
            'subgroup' => implode(',', $groups),
            'db_mountpoints' => implode(',', $db_mounts),
            'TSconfig' => $tsconfig,
            'pid' => 0,
        ];

        return $id;
    }

    /**
     * @param  string $username
     * @param  string $password
     * @param  string $realName
     * @param  string $email
     * @param  array  $groups
     * @return int
     */
    protected function addBEUser($username, $password, $realName, $email, $groups = [])
    {
        $id = $this->allocateId('user-' . $username);

        $this->data['be_users'][$id] = [
            'username'  => $username,
            'password'  => $password,
            'realName'  => $realName,
            'email'     => $email,
            'usergroup' => implode(',', $groups),
            'options'   => 3,
            'pid'       => 0,
        ];

        return $id;
    }

    /**
     * Allocate a temporary new-id identifier on $this->pool
     *
     * @param  string $identifier
     * @return string
     */
    protected function allocateId($identifier)
    {
        if (array_search($identifier, $this->pool) !== false) {
            throw new \RuntimeException(sprintf("Internal Error: Duplicate identifier: %s\n", $identifier), 1453977589);
        }

        return 'NEW' . (array_push($this->pool, $identifier) - 1);
    }

    /**
     * @param  string $identifier
     * @return string
     */
    protected function getId($identifier)
    {
        $id = array_search($identifier, $this->pool);
        if ($id === false) {
            throw new \RuntimeException(sprintf("Internal Error: Temporary ID '%s' not found\n", $identifier), 1453977590);
        }

        return 'NEW' . array_search($identifier, $this->pool);
    }

    /*
     * @param array $data
     * @param array $cmd
     *
     * @return DataHandler
     */
    protected function createDataHandler($data = [], $cmd = [])
    {
        $tce = GeneralUtility::makeInstance(DataHandler::class);
        $tce->stripslashes_values = 0;
        $TCAdefaultOverride = $GLOBALS['BE_USER']->getTSConfigProp('TCAdefaults');
        if (is_array($TCAdefaultOverride)) {
            $tce->setDefaultsFromUserTS($TCAdefaultOverride);
        }
        $tce->start($data, $cmd);

        return $tce;
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @return \TYPO3\CMS\Core\Resource\StorageRepository
     */
    protected function getStorageRepository()
    {
        return GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\StorageRepository::class);
    }

    /**
     * @return \Qbus\SubsiteGenerator\Service\ConfigurationService
     */
    protected function getConfig()
    {
        return GeneralUtility::makeInstance(\Qbus\SubsiteGenerator\Service\ConfigurationService::class);
    }
}
