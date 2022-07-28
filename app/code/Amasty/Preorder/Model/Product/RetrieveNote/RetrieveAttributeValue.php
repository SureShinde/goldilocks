<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\RetrieveNote;

use Amasty\Preorder\Model\Utils\StripTags;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObjectFactory;

class RetrieveAttributeValue
{
    /**
     * @var StripTags
     */
    private $stripTags;

    /**
     * @var FormatNote
     */
    private $formatNote;

    /**
     * @var DefaultValuePool
     */
    private $defaultValuePool;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    public function __construct(
        StripTags $stripTags,
        FormatNote $formatNote,
        DefaultValuePool $defaultValuePool,
        ManagerInterface $eventManager,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->stripTags = $stripTags;
        $this->formatNote = $formatNote;
        $this->defaultValuePool = $defaultValuePool;
        $this->eventManager = $eventManager;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @param ProductInterface|Product $product
     * @param string $code
     * @return string
     */
    public function execute(ProductInterface $product, string $code): string
    {
        $note = $product->hasData($code)
            ? $product->getData($code)
            : $product->getResource()->getAttributeRawValue($product->getId(), $code, $product->getStoreId());

        if (!$note) {
            $note = $this->defaultValuePool->getRetriever($code)->execute();
        }

        $transportObject = $this->dataObjectFactory->create(['data' => [
            'value' => $note,
            'product' => $product
        ]]);
        $this->eventManager->dispatch($code . '_value_loaded', ['transport' => $transportObject]);
        $note = $transportObject->getData('value');

        if ($note) {
            $note = $this->stripTags->execute($note, StripTags::ALLOWED_TAGS);
            $note = $this->formatNote->execute($note, $product);
        }

        return $note;
    }
}
