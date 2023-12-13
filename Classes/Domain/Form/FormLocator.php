<?php

/*
 * This file is part of the Sitegeist.StoneTablet package.
 */

declare(strict_types=1);

namespace Sitegeist\StoneTablet\Domain\Form;

use Neos\ContentRepository\Domain\Model\Node;
use Neos\ContentRepository\Domain\Model\NodeInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Neos\Domain\Service\ContentContext;

#[Flow\Proxy(false)]
class FormLocator
{
    public function __construct(
        public readonly string $formId,
        public readonly ?string $title,
        public readonly string $path
    ) {
    }

    public static function fromFormIdentifier(
        string $formId,
        ContentContext $subgraph
    ): self {
        /** @var Node $formNode */
        $formNode = $subgraph->getNodeByIdentifier($formId);
        $path = implode(
            ' > ',
            array_map(
                fn(NodeInterface $nodeOnPath) => $nodeOnPath->getLabel(),
                array_filter(
                    $subgraph->getNodesOnPath(
                        $subgraph->getCurrentSiteNode(),
                        $formNode
                    ),
                    fn (NodeInterface $node) => $node->getNodeType()->isOfType('Neos.Neos:Document')
                )
            )
        );

        $formTitle = $formNode->getProperty('formTitle') ?? $formNode->getLabel();

        return new self(
            $formId,
            $formTitle,
            $path
        );
    }
}
