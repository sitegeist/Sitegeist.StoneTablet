<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Domain\Form;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class Field implements \JsonSerializable
{
    public function __construct(
        public readonly string $name
    ) {
    }

    /**
     * @param array<string,string> $array
     */
    public static function fromArray(array $array): self
    {
        return new self(
            $array['name']
        );
    }

    /**
     * @return array<string,string|null>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name
        ];
    }
}
