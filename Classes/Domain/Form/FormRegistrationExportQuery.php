<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Domain\Form;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class FormRegistrationExportQuery
{
    private function __construct(
        public readonly \DateTimeImmutable $startDate,
        public readonly \DateTimeImmutable $endDate,
        public readonly ?string $formId
    ) {
    }

    public static function fromExportRequest(
        ?string $startDateString,
        ?string $endDateString,
        string $formId
    ): self {
        $startDate = $startDateString
            ? new \DateTimeImmutable(
                $startDateString,
                new \DateTimeZone('Europe/Berlin')
            )
            : new \DateTimeImmutable(
                '2000-01-01',
                new \DateTimeZone('Europe/Berlin')
            );
        $startDate = $startDate->setTime(0, 0, 0);

        $endDate = $endDateString
            ? new \DateTimeImmutable(
                $endDateString,
                new \DateTimeZone('Europe/Berlin')
            )
            : new \DateTimeImmutable(
                'now',
                new \DateTimeZone('Europe/Berlin')
            );
        $endDate = $endDate->setTime(0, 0, 0)->modify('+1 day');

        return new self(
            $startDate,
            $endDate,
            $formId
        );
    }
}
