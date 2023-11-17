<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Domain\Form;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ResourceManagement\PersistentResource;

/**
 * The PersistedUploadFile domain value object
 */
#[Flow\Proxy(false)]
final class RegisteredUploadField implements \Stringable
{
    const CELL_PREFIX = '#upload_field_';
    public function __construct(
        public readonly string $value
    ) {
    }
    public static function fromResource(?PersistentResource $resource): self
    {
        return new self(
            $resource ? self::CELL_PREFIX . $resource->getSha1() . '.' .  $resource->getFileExtension() : ''
        );
    }
    public function extractExportFileName(): string
    {
        return substr($this->value, strlen(self::CELL_PREFIX));
    }

    public function extractSha1(): string
    {
        return strtok(
            substr($this->value, strlen(self::CELL_PREFIX)),
            '.'
        );
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
