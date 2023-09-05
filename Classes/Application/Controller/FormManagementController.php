<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Application\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Fusion\View\FusionView;
use Neos\Neos\Controller\Module\AbstractModuleController;
use Sitegeist\StoneTablet\Domain\Form\FormDirectory;
use Sitegeist\StoneTablet\Domain\Form\FormRegistrationExport;
use Sitegeist\StoneTablet\Domain\Form\FormRegistrationExportQuery;
use Sitegeist\StoneTablet\Domain\Form\FormRegistrationRepository;
use Sitegeist\StoneTablet\Domain\Form\FormWasNotFound;

class FormManagementController extends AbstractModuleController
{
    /**
     * @var string
     */
    protected $defaultViewObjectName = FusionView::class;

    #[Flow\Inject]
    protected FormRegistrationRepository $formRegistrationRepository;

    #[Flow\Inject]
    protected FormDirectory $formDirectory;

    public function indexAction(): void
    {
        $this->view->assignMultiple(
            [
                'targetAction' => 'export',
                'formLocators' => $this->formDirectory->findFormLocators()
            ]
        );
    }

    public function exportAction(string $formId, ?string $startDate, ?string $endDate): void
    {
        $registrations = $this->formRegistrationRepository->findByExportQuery(
            FormRegistrationExportQuery::fromExportRequest(
                $startDate,
                $endDate,
                $formId
            )
        );
        $formLocator = $this->formDirectory->findFormLocatorByIdentifier($formId);

        if (!$formLocator) {
            throw FormWasNotFound::butWasAskedForExport($formId);
        }

        $export = new FormRegistrationExport(
            new \DateTimeImmutable(),
            $registrations,
            $formLocator,
            $this->formDirectory
        );


        ob_clean();
        $content = $export->createExcelContent();
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header(
            'Content-Disposition: attachment; filename='
            . ($formLocator->title ?: $formLocator->formId) . '-Export-'
            . ($startDate ? $startDate . '_' : '')  . ($endDate ?: $export->exportDate->format('Y-m-d'))
            . '.xlsx'
        );
        header("Content-Length: " . strlen($content));
        echo($content);
        exit();
    }

    protected function initializeView(ViewInterface $view): void
    {
        parent::initializeView($view);
        /** @var FusionView $view */
        $view->setFusionPathPattern(
            'resource://Sitegeist.StoneTablet/Private/Fusion/Integration/FormManagement'
        );
        $view->assignMultiple([
            'flashMessages' => $this->controllerContext->getFlashMessageContainer()->getMessagesAndFlush()
        ]);
    }
}
