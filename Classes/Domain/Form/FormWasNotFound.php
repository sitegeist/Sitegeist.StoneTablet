<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Domain\Form;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class FormWasNotFound extends \RuntimeException
{
    public static function butWasAskedForExport(string $attemptedValue): self
    {
        return new self(
            'The form with ' . $attemptedValue
                . ' identifier was not found.',
            1692780654
        );
    }
}
