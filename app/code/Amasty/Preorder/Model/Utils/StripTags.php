<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Utils;

use Magento\Framework\Filter\FilterManager;

class StripTags
{
    public const ALLOWED_TAGS
        = '<b><a><i><strong><blockquote><del><em><img><kbd><p><s><sup><sub><br><hr><ul><li><h1><h2><h3><dd><dl><span>';

    /**
     * @var FilterManager
     */
    private $filterManager;

    public function __construct(FilterManager $filterManager)
    {
        $this->filterManager = $filterManager;
    }

    /**
     * Wrapper for standard strip_tags() function with extra functionality for html entities
     *
     * @param string $data
     * @param string|null $allowableTags
     * @param bool $allowHtmlEntities
     * @return string
     */
    public function execute(
        string $data,
        ?string $allowableTags = null,
        bool $allowHtmlEntities = false
    ): string {
        return $this->filterManager->stripTags(
            $data,
            ['allowableTags' => $allowableTags, 'escape' => $allowHtmlEntities]
        );
    }
}
