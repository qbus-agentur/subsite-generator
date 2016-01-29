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
     * @param  SubsiteGeneratorService $subsiteGeneratorService
     * @return void
     */
    public function injectSubsiteGeneratorService(SubsiteGeneratorService $subsiteGeneratorService)
    {
        $this->subsiteGeneratorService = $subsiteGeneratorService;
    }

    /**
     * action new
     *
     * @return void
     */
    protected function newAction()
    {
    }

    /**
     * @param  array $formdata
     * @return void
     */
    protected function createAction($formdata)
    {
        if (!isset($formdata['title'])) {
            $this->redirect('new');
        }

        $status = $this->subsiteGeneratorService->create(
            $formdata['title'],
            $formdata['subdomain'],
            $formdata['uName'], $formdata['uPhone'], $formdata['uMail'], $formdata['uAccount'], $formdata['uPassword']
        );

        if ($status) {
            $this->addFlashMessage("Subsite '" . $formdata['title'] . "' has been created.");
        }
        $this->redirect('new');
    }
}
