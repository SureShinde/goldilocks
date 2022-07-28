<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\ResourceModel\Product;

use Amasty\PreOrderRelease\Model\ConfigProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;

class LoadIdsWithExpiredReleaseDate
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig,
        MetadataPool $metadataPool,
        ConfigProvider $configProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->eavConfig = $eavConfig;
        $this->metadataPool = $metadataPool;
        $this->configProvider = $configProvider;
    }

    public function execute(string $currentDate): array
    {
        $connection = $this->resourceConnection->getConnection();

        $attribute = $this->eavConfig->getAttribute(
            Product::ENTITY,
            $this->configProvider->getReleaseDateAttribute()
        );
        $productMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $productMetadata->getLinkField();

        $select = $connection->select()->from(
            ['attr' => $this->resourceConnection->getTableName(['catalog_product_entity', 'datetime'])],
            []
        )->join(
            ['cpe' => $this->resourceConnection->getTableName('catalog_product_entity')],
            sprintf('cpe.%1$s= attr.%1$s', $linkField),
            ['entity_id']
        )->where(
            'attr.attribute_id = ?',
            $attribute->getId()
        )->where(
            'attr.store_id = ?',
            Store::DEFAULT_STORE_ID
        )->where(
            'attr.value = ?',
            $connection->getDateFormatSql($connection->quote($currentDate), '%Y-%m-%d %H:%i:%s')
        );

        return $connection->fetchCol($select);
    }
}
