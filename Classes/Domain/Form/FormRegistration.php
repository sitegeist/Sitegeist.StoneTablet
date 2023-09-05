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
final class FormRegistration implements \JsonSerializable
{
    /**
     * @param array<string,mixed> $formData
     */
    public function __construct(
        public readonly string $identifier,
        public readonly string $formId,
        public readonly array $formData,
        public readonly \DateTimeImmutable $recordedAt
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'identifier' => $this->identifier,
            'formId' => $this->formId,
            'formData' => $this->formData,
            'recordedAt' => $this->recordedAt
        ];
    }
}
