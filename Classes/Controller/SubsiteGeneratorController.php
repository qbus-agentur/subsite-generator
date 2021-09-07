<?php
namespace Qbus\SubsiteGenerator\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Qbus\SubsiteGenerator\Service\ConfigurationService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Lang\LanguageService;
use Qbus\SubsiteGenerator\Service\SubsiteGeneratorService;


/**
 * SubsiteGeneratorController
 *
 * @author Benjamin Franzke <bfr@qbus.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SubsiteGeneratorController extends ActionController
{
    /**
     * @var SubsiteGeneratorService
     */
    protected $subsiteGeneratorService;

    /**
     * @var \Qbus\SubsiteGenerator\Service\ConfigurationService
     */
    protected $configurationService;

    /**
     * @param  SubsiteGeneratorService $subsiteGeneratorService
     * @return void
     */
    public function injectSubsiteGeneratorService(SubsiteGeneratorService $subsiteGeneratorService)
    {
        $this->subsiteGeneratorService = $subsiteGeneratorService;
    }

    /**
     * @param  \Qbus\SubsiteGenerator\Service\ConfigurationService $configurationService
     * @return void
     */
    public function injectConfigurationService(ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * BackendTemplateView Container
     *
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * action new
     *
     * @return void
     */
    protected function newAction()
    {
        $this->view->assign('domainSuffix', $this->configurationService->get('domain_suffix'));
    }

    /**
     * @param  array $formdata
     * @return void
     */
    protected function createAction($formdata)
    {
        foreach (['title', 'subdomain'] as $field) {
            if (!isset($formdata[$field]) || $formdata[$field] == '') {
                $this->addFlashMessage(
                    'Not all required fields were set',
                    '',
                    FlashMessage::ERROR
                );
                $this->redirect('new');
            }
        }

        $status = $this->subsiteGeneratorService->create(
            $formdata['title'],
            $formdata['subdomain'],
            $formdata['uAccount'],
            $formdata['uPassword'],
            $formdata['uName'],
            $formdata['uMail']
        );

        if ($status) {
            $this->addFlashMessage("Subsite '" . $formdata['title'] . "' has been created.");
        }
        $this->redirect('new');
    }

    /**
     * Initialize the view
     *
     * @param ViewInterface $view The view
     */
    protected function initializeView(ViewInterface $view)
    {
        if ($view === null) {
            return;
        }
        /** @var BackendTemplateView $view */
        parent::initializeView($view);
        if ($view->getModuleTemplate() === null) {
            return;
        }
        $view->getModuleTemplate()->getDocHeaderComponent()->setMetaInformation([]);

        $lang = $this->getLanguageService();
        $lang->includeLLFile('EXT:subsite_generator/Resources/Private/Language/locallang.xlf');
        $this->shortcutName = $lang->getLL('module_index');

        //$this->generateMenu();
        $this->generateButtons();
    }

    /**
     * Gets all buttons for the docheader
     */
    protected function generateButtons()
    {
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();
        $moduleName = $this->request->getPluginName();
        $getVars = $this->request->hasArgument('getVars') ? $this->request->getArgument('getVars') : [];
        $setVars = $this->request->hasArgument('setVars') ? $this->request->getArgument('setVars') : [];
        if (count($getVars) === 0) {
            $modulePrefix = strtolower('tx_' . $this->request->getControllerExtensionName() . '_' . $moduleName);
            $getVars = ['id', 'M', $modulePrefix];
        }
        $shortcutButton = $buttonBar->makeShortcutButton()
            ->setModuleName($moduleName)
            ->setGetVariables($getVars)
            ->setDisplayName($this->shortcutName)
            ->setSetVariables($setVars);
        $buttonBar->addButton($shortcutButton);
    }

    /**
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

}
