<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Runtime\Action;

use Neos\ContentRepository\Domain\Model\Node;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Utility\Algorithms;
use Neos\Fusion\Form\Runtime\Action\AbstractAction;
use Sitegeist\StoneTablet\Domain\Form\Field;
use Sitegeist\StoneTablet\Domain\Form\FormRegistration;
use Sitegeist\StoneTablet\Domain\Form\FormRegistrationRepository;

class RegisterFormAction extends AbstractAction
{
    public const DATE_FORMAT = 'Y-m-d';
    public function __construct(
        private readonly FormRegistrationRepository $formRegistrationRepository
    ) {
    }

    public function perform(): ?ActionResponse
    {
        /** @var Node $formNode */
        $formNode = $this->options['formNode'];

        $formData = $this->prepareFormDataForSerialisation($this->options['formData']);

        $excludedFields = array_map(
            fn($excludedField) => Field::fromArray($excludedField)->name,
            $formNode->getProperty('excludedFields') ?: []
        );

        foreach ($formData as $fieldName => $fieldValue) {
            if (is_object($fieldValue)) {
                $excludedFields[] = $fieldName;
            }
        }

        $this->formRegistrationRepository->add(
            new FormRegistration(
                Algorithms::generateUUID(),
                (string)$formNode->getNodeAggregateIdentifier(),
                array_diff_key($formData, array_flip($excludedFields)),
                new \DateTimeImmutable("now", new \DateTimeZone('Europe/Berlin'))
            )
        );

        return null;
    }

    /**
     * @param array<string,mixed> $formData
     * @return array<string,mixed>
     */
    private function prepareFormDataForSerialisation(array $formData): array
    {
        return array_map(
            fn (mixed $fieldValue) => $fieldValue instanceof \DateTimeInterface
                ? $fieldValue->format(self::DATE_FORMAT)
                : $fieldValue,
            $formData
        );
    }
}
