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
        public readonly FormRegistrations  $registrations,
        public readonly FormLocator        $formLocator,
        public readonly FormDirectory      $formDirectory,
        private readonly Archive $archive
    ) {
    }

    public function createExcelContent(): string
    {
        $fieldNames = $this->getFieldNames($this->registrations);
        $spreadsheet = new Spreadsheet();
        $metaHeaderRow = $spreadsheet->getActiveSheet()->getRowIterator(1)->current();
        $currentRowIndex = $metaHeaderRow->getRowIndex();
        $metaHeaderRow->getWorksheet()->getCell('A' . $currentRowIndex)->setValue('Title');
        $metaHeaderRow->getWorksheet()->getCell('B' . $currentRowIndex)->setValue(
            $this->formLocator->title
                ? $this->formLocator->title . ' (' . $this->formLocator->formId . ')'
                : $this->formLocator->formId
        );
        $spreadsheet->getActiveSheet()->insertNewRowBefore(2, 1);
        $headerRow = $spreadsheet->getActiveSheet()->getRowIterator(2)->current();
        $currentRowIndex = $headerRow->getRowIndex();
        $metaHeaderRow->getWorksheet()->getCell('A' . $currentRowIndex)->setValue('Path');
        $metaHeaderRow->getWorksheet()->getCell('B' . $currentRowIndex)->setValue(
            $this->formLocator->path ?: ''
        );
        $spreadsheet->getActiveSheet()->insertNewRowBefore(3, 1);
        $headerRow = $spreadsheet->getActiveSheet()->getRowIterator(3)->current();
        $currentRowIndex = $headerRow->getRowIndex();
        $metaHeaderRow->getWorksheet()->getCell('A' . $currentRowIndex)->setValue('Export Date');
        $metaHeaderRow->getWorksheet()->getCell('B' . $currentRowIndex)->setValue(
            $this->exportDate->format('Y-m-d H:i:s')
        );

        $spreadsheet->getActiveSheet()->insertNewRowBefore(4, 1);
        $headerRow = $spreadsheet->getActiveSheet()->getRowIterator(4)->current();
        $currentRowIndex = $headerRow->getRowIndex();
        $column = 'A';
        $metaHeaderRow->getWorksheet()->getCell($column . $currentRowIndex)->setValue('Identifier');
        $column++;
        foreach ($fieldNames as $fieldName) {
            $metaHeaderRow->getWorksheet()->getCell($column . $currentRowIndex)->setValue($fieldName);
            $column++;
        }
        if ($fieldNames) {
            $metaHeaderRow->getWorksheet()->getCell($column . $currentRowIndex)->setValue('Request Date');
        }
        $currentRowIndex = 5;
        $spreadsheet->getActiveSheet()->insertNewRowBefore(5, $this->registrations->count());
        $exportDirectory = ExportDirectory::create();

        foreach ($this->registrations as $registration) {
            $column = 'A';
            $metaHeaderRow->getWorksheet()->getCell(
                $column . $currentRowIndex
            )->setValue(
                $registration->identifier
            );
            $column++;
            $currentRegistrationFields = $registration->formData;

            foreach ($fieldNames as $fieldName) {
                $fieldValue = $currentRegistrationFields[$fieldName] ?? null;

                if ($fieldValue && str_starts_with($fieldValue, RegisteredUploadField::CELL_PREFIX) ) {
                    $registeredUploadField = new RegisteredUploadField($fieldValue);

                    $fileName = $this->archive->exportResourceFromRegisteredUploadField(
                        $registeredUploadField,
                        $exportDirectory
                    );

                    $metaHeaderRow->getWorksheet()->getCell(
                        $column . $currentRowIndex
                    )->getHyperlink()->setUrl('./' . ExportDirectory::UPLOADS . $registeredUploadField->extractExportFileName());

                    $metaHeaderRow->getWorksheet()->getCell(
                        $column . $currentRowIndex
                    )->setValue(
                        $fileName
                    );
                } else {
                    $metaHeaderRow->getWorksheet()->getCell(
                        $column . $currentRowIndex
                    )->setValue(
                        CellValue::fromFormData($currentRegistrationFields, $fieldName)->value
                    );
                }

                $column++;
            }
            $metaHeaderRow->getWorksheet()->getCell(
                $column . $currentRowIndex
            )->setValue(
                $registration->recordedAt->format(FormRegistrationRepository::DATE_FORMAT)
            );

            $currentRowIndex++;
        }
        $excelPathAndName = $exportDirectory->path . basename($exportDirectory->path) .  '.xlsx';
        $writer = new ExcelWriter($spreadsheet);
        $writer->save($excelPathAndName);

        $zipFilePath = $this->archive->compressExportDirectory($exportDirectory);

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
