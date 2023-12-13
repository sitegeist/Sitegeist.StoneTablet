<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Domain\Form;

use Neos\ContentRepository\Domain\Model\Node;
use Neos\Eel\FlowQuery\FlowQuery;
use Neos\Neos\Domain\Service\ContentContextFactory;
use Neos\Neos\Domain\Service\ContentDimensionPresetSourceInterface;

/**
 * The ContactRegistration domain value object
 */

final class FormDirectory
{
    public function __construct(
        private readonly ContentContextFactory $contentContextFactory,
        private readonly ContentDimensionPresetSourceInterface $contentDimensionPresetSource
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function findFormLocators(): array
    {
        $formLocators = [];
        foreach ($this->getContentContexts() as $contentContext) {
            /** @var Node $siteNode */
            $siteNode = $contentContext->getCurrentSiteNode();

            /** @phpstan-ignore-next-line */
            $formNodes = (new FlowQuery([$siteNode]))->find(
                '[instanceof Sitegeist.StoneTablet:Mixin.ExportableForm]'
            )->get();

            foreach ($formNodes as $formNode) {
                if ($formNode->getProperty('isExportable')) {
                    $nodeIdentifier = (string)$formNode->getNodeAggregateIdentifier();
                    if (!isset($formLocators[$nodeIdentifier])) {
                        $formLocators[$nodeIdentifier] = FormLocator::fromFormIdentifier(
                            $nodeIdentifier,
                            $contentContext
                        );
                    }
                }

            }
        }
        return $formLocators;
    }

    public function findFormLocatorByIdentifier(string $formId): ?FormLocator
    {
        foreach ($this->getContentContexts() as $contentContext) {
            $formNode = $contentContext->getNodeByIdentifier($formId);
            if ($formNode) {
                return FormLocator::fromFormIdentifier(
                    $formId,
                    $contentContext
                );
            }
        }
        return null;
    }

    private function getContentContexts(): \Iterator
    {
        $languagePresets = $this->contentDimensionPresetSource->getAllPresets()['language']['presets'] ?? null;

        if (empty($languagePresets)) {
            $contentContext = $this->contentContextFactory->create([
                'dimensions' => [],
                'targetDimensions' => [],
                'invisibleContentShown' => true,
                'inaccessibleContentShown' => true
            ]);
            yield $contentContext;
        } else {
            foreach (array_keys($languagePresets) as $preset) {
                $languageValues = $languagePresets[$preset]['values'];

                yield $contentContext = $this->contentContextFactory->create([
                    'dimensions' => ['language' => $languageValues],
                    'targetDimensions' => ['language' => reset($languageValues)],
                    'invisibleContentShown' => true,
                    'inaccessibleContentShown' => true
                ]);
            }
        }
    }
}
