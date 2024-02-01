<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Domain\Form;

use Neos\Flow\Annotations as Flow;
use Neos\Utility\Files;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as ExcelWriter;
use Sitegeist\StoneTablet\Domain\ExportDirectory;
use Sitegeist\StoneTablet\Domain\Archive;

#[Flow\Proxy(false)]
final class FormRegistrationExport
{
    public function __construct(
        public readonly \DateTimeImmutable $exportDate,
        public readonly FormRegistrations $registrations,
        public readonly FormLocator $formLocator,
        public readonly FormDirectory $formDirectory,
        private readonly Archive $archive
    ) {
    }

    public function createExcelContent(): string
    {
        $fieldNames = $this->getFieldNames($this->registrations);
        $spreadsheet = new Spreadsheet();
        $exportDirectory = ExportDirectory::create();

        // Add some data
        $spreadsheet
            ->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Title')
            ->setCellValue(
                'B1',
                $this->formLocator->title
                    ? $this->formLocator->title .
                        ' (' .
                        $this->formLocator->formId .
                        ')'
                    : $this->formLocator->formId
            )
            ->setCellValue('A2', 'Path')
            ->setCellValue('B2', $this->formLocator->path ?: '')
            ->setCellValue('A3', 'Export Date')
            ->setCellValue('B3', $this->exportDate->format('Y-m-d H:i:s'))
            ->setCellValue('A4', 'Identifier');

        $column = 'B';
        foreach ($fieldNames as $fieldName) {
            $spreadsheet
                ->getActiveSheet()
                ->setCellValue($column . '4', $fieldName);
            $column++;
        }
        if ($fieldNames) {
            $spreadsheet
                ->getActiveSheet()
                ->setCellValue($column . '4', 'Request Date');
        }

        $currentRowIndex = 5;
        foreach ($this->registrations as $registration) {
            $column = 'A';
            $spreadsheet
                ->getActiveSheet()
                ->setCellValue(
                    $column . $currentRowIndex,
                    $registration->identifier
                );
            $column++;
            $currentRegistrationFields = $registration->formData;
            foreach ($fieldNames as $fieldName) {
                $fieldValue = $currentRegistrationFields[$fieldName] ?? null;

                if (
                    $fieldValue &&
                    str_starts_with(
                        $fieldValue,
                        RegisteredUploadField::CELL_PREFIX
                    )
                ) {
                    $registeredUploadField = new RegisteredUploadField(
                        $fieldValue
                    );

                    $fileName = $this->archive->exportResourceFromRegisteredUploadField(
                        $registeredUploadField,
                        $exportDirectory
                    );

                    $spreadsheet
                        ->getActiveSheet()
                        ->getCell($column . $currentRowIndex)
                        ->setValue($fileName)
                        ->getHyperlink()
                        ->setUrl(
                            './' .
                                ExportDirectory::UPLOADS .
                                $registeredUploadField->extractExportFileName()
                        );
                } else {
                    $spreadsheet
                        ->getActiveSheet()
                        ->setCellValue(
                            $column . $currentRowIndex,
                            CellValue::fromFormData(
                                $currentRegistrationFields,
                                $fieldName
                            )->value
                        );
                }
                $column++;
            }
            $spreadsheet
                ->getActiveSheet()
                ->setCellValue(
                    $column . $currentRowIndex,
                    $registration->recordedAt->format(
                        FormRegistrationRepository::DATE_FORMAT
                    )
                );
            $currentRowIndex++;
        }

        $excelPathAndName =
            $exportDirectory->path . basename($exportDirectory->path) . '.xlsx';
        $writer = new ExcelWriter($spreadsheet);
        $writer->save($excelPathAndName);

        $zipFilePath = $this->archive->compressExportDirectory(
            $exportDirectory
        );

        if ($zipFilePath) {
            $result = Files::getFileContents($zipFilePath);
            Files::removeDirectoryRecursively(ExportDirectory::EXPORT_ROOT);
            return $result;
        }

        return '';
    }

    /**
     * @return array<int,string>
     */
    public function getFieldNames(FormRegistrations $registrations): array
    {
        $fieldNames = [];
        foreach ($registrations as $registration) {
            foreach ($registration->formData as $fieldName => $fieldValue) {
                if (!in_array($fieldName, $fieldNames)) {
                    $fieldNames[] = $fieldName;
                }
            }
        }
        return $fieldNames;
    }
}
