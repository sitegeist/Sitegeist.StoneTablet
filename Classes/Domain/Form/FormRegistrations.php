<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Domain\Form;

use Neos\Flow\Annotations as Flow;

/**
 * @implements \IteratorAggregate<int,FormRegistration>
 */
#[Flow\Proxy(false)]
final class FormRegistrations implements \IteratorAggregate, \Countable
{
    /**
     * @var array<int,FormRegistration>
     */
    private array $registrations;

    public function __construct(FormRegistration ...$registrations)
    {
        /** @phpstan-var array<int,FormRegistration>  $registrations*/
        $this->registrations = $registrations;
    }

    /**
     * @return \ArrayIterator<int,FormRegistration>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->registrations);
    }

    public function count(): int
    {
        return count($this->registrations);
    }
}
