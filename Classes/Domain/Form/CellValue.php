<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Domain\Form;

use Neos\Flow\Annotations as Flow;

/**
 * The FormRegistration domain value object
 */
#[Flow\Proxy(false)]
final class CellValue
{
    public function __construct(
        public readonly mixed $value
    ) {
    }

    /**
     * @param array<string,mixed> $formData
     */
    public static function fromFormData(array $formData, string $fieldName): self
    {
        $fieldValue = $formData[$fieldName] ?? null;

        return new self(
            $fieldValue
                ? is_array($fieldValue) ? implode("\n", $fieldValue) : $fieldValue
                : ''
        );
    }
}
