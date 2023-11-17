<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Utility\Files;

#[Flow\Proxy(false)]
class ExportDirectory
{
    public const EXPORT_ROOT = FLOW_PATH_DATA . 'Temporary/StoneTablet/';
    public const UPLOADS = 'Uploads/';
    public function __construct(
        public readonly string $path,
    ) {
    }

    public static function create(): self {
        $date = new \DateTime('now');
        $directoryPathAndName = self::EXPORT_ROOT . 'Export-' . $date->format('Y-m-d_H-i') . '/';

        if (file_exists($directoryPathAndName)) {
            Files::removeDirectoryRecursively($directoryPathAndName);
        }

        Files::createDirectoryRecursively($directoryPathAndName . self::UPLOADS);

        return new self(
            $directoryPathAndName . '/'
        );
    }

    public function getUploadPath(): string
    {
        return $this->path . self::UPLOADS;
    }
}
