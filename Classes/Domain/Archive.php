<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\ResourceRepository;
use Neos\Utility\Files;
use Sitegeist\StoneTablet\Domain\Form\RegisteredUploadField;

final class Archive
{
    #[Flow\Inject]
    protected ResourceRepository $resourceRepository;

    public function exportResourceFromRegisteredUploadField(
        RegisteredUploadField $registeredUploadField,
        ExportDirectory $exportDirectory
    ): string {
        $resourceSha1Hash = $registeredUploadField->extractSha1();

        $uploadFile = $this->resourceRepository->findOneBySha1($resourceSha1Hash);

        if ($uploadFile) {
            $resourceStream = $uploadFile->getStream();

            if($resourceStream) {
                file_put_contents(
                    $exportDirectory->getUploadPath() . $registeredUploadField->extractExportFileName(),
                    stream_get_contents($resourceStream)
                );
                fclose($resourceStream);
            }
            return $uploadFile->getFilename();
        }

        return '';
    }

    public function compressExportDirectory(ExportDirectory $exportDirectory): ?string
    {
        $rootPath = realpath($exportDirectory->path);
        $archivePath = $exportDirectory->path . 'Archive.zip';
        $zip = new \ZipArchive();

        if ($zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            foreach (Files::readDirectoryRecursively($exportDirectory->path) as $filePath) {
                if (!is_dir($filePath))
                {
                    $relativePath = substr($filePath, strlen($rootPath) + 1);

                    // Add current file to archive
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            return $archivePath;
        }

        return null;
    }
}
