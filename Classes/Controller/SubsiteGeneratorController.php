<?php
namespace Qbus\SubsiteGenerator\Controller;

use Qbus\SubsiteGenerator\Service\SubsiteGeneratorService;

/**
 * SubsiteGeneratorController
 *
 * @author Benjamin Franzke <bfr@qbus.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class SubsiteGeneratorController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
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
    public function injectConfigurationService(\Qbus\SubsiteGenerator\Service\ConfigurationService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

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
        foreach (['title', 'subdomain', 'uAccount', 'uPassword'] as $field) {
            if (!isset($formdata[$field]) || $formdata[$field] == '') {
                $this->addFlashMessage('Not all required fields were set');
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
}
