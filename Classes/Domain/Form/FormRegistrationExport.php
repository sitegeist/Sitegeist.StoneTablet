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

#[Flow\Proxy(false)]
final class FormRegistrationExport
{
    public function __construct(
        public readonly \DateTimeImmutable $exportDate,
        public readonly FormRegistrations  $registrations,
        public readonly FormLocator        $formLocator,
        public readonly FormDirectory      $formDirectory
    ) {
    }

    public function createExcelContent(): string
    {
        $fieldNames = $this->getFieldNames($this->registrations);
        $spreadsheet = new Spreadsheet();
        $metaHeaderRow = $spreadsheet->getActiveSheet()->getRowIterator(1)->current();
        $currentRowIndex = $metaHeaderRow->getRowIndex();
        $metaHeaderRow->getWorksheet()->getCell('A' . $currentRowIndex)->setValue('Titel');
        $metaHeaderRow->getWorksheet()->getCell('B' . $currentRowIndex)->setValue(
            $this->formLocator->title
                ? $this->formLocator->title . ' (' . $this->formLocator->formId . ')'
                : $this->formLocator->formId
        );
        $spreadsheet->getActiveSheet()->insertNewRowBefore(2, 1);
        $headerRow = $spreadsheet->getActiveSheet()->getRowIterator(2)->current();
        $currentRowIndex = $headerRow->getRowIndex();
        $metaHeaderRow->getWorksheet()->getCell('A' . $currentRowIndex)->setValue('Pfad');
        $metaHeaderRow->getWorksheet()->getCell('B' . $currentRowIndex)->setValue(
            $this->formLocator->path ?: ''
        );
        $spreadsheet->getActiveSheet()->insertNewRowBefore(3, 1);
        $headerRow = $spreadsheet->getActiveSheet()->getRowIterator(3)->current();
        $currentRowIndex = $headerRow->getRowIndex();
        $metaHeaderRow->getWorksheet()->getCell('A' . $currentRowIndex)->setValue('Datum des Exports');
        $metaHeaderRow->getWorksheet()->getCell('B' . $currentRowIndex)->setValue(
            $this->exportDate->format('Y-m-d H:i:s')
        );

        $spreadsheet->getActiveSheet()->insertNewRowBefore(4, 1);
        $headerRow = $spreadsheet->getActiveSheet()->getRowIterator(4)->current();
        $currentRowIndex = $headerRow->getRowIndex();
        $column = 'A';
        foreach ($fieldNames as $fieldName) {
            $metaHeaderRow->getWorksheet()->getCell($column . $currentRowIndex)->setValue($fieldName);
            $column++;
        }
        if ($fieldNames) {
            $metaHeaderRow->getWorksheet()->getCell($column . $currentRowIndex)->setValue('Anfragedatum');
        }
        $currentRowIndex = 5;
        $spreadsheet->getActiveSheet()->insertNewRowBefore(5, $this->registrations->count());
        foreach ($this->registrations as $registration) {
            $column = 'A';
            $currentRow = $spreadsheet->getActiveSheet()->getRowIterator($currentRowIndex)->current();
            $currentRegistrationFields = $registration->formData;
            foreach ($fieldNames as $fieldName) {
                $metaHeaderRow->getWorksheet()->getCell(
                    $column . $currentRowIndex
                )->setValue(
                    CellValue::fromFormData($currentRegistrationFields, $fieldName)->value
                );
                $column++;
            }
            $metaHeaderRow->getWorksheet()->getCell(
                $column . $currentRowIndex
            )->setValue(
                $registration->recordedAt->format(FormRegistrationRepository::DATE_FORMAT)
            );
            $currentRowIndex++;
        }

        $date = new \DateTime('now');
        $filePathAndName =
            /** @phpstan-ignore-next-line */
            FLOW_PATH_DATA .
            'Temporary/Form-Export-'
            . $date->format('Y-m-d_H-i') .  '.xlsx';
        $writer = new ExcelWriter($spreadsheet);
        $writer->save($filePathAndName);
        $result = Files::getFileContents($filePathAndName);
        unlink($filePathAndName);

        return $result;
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
