<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\DeliveryChannel;

use Amasty\DeliveryDateManager\Api\Data\DeliveryChannelInterface;
use Amasty\DeliveryDateManager\Model\AbstractTypifiedModel;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel as DeliveryChannelResource;
use Amasty\DeliveryDateManager\Model\ResourceModel\DeliveryChannel\Collection as DeliveryChannelCollection;
use Amasty\DeliveryDateManager\Model\Validator\ValidatorComposite;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * @method DeliveryChannelResource getResource()
 * @method DeliveryChannelCollection getCollection()
 */
class DeliveryChannelData extends AbstractTypifiedModel implements DeliveryChannelInterface
{
    public const CACHE_TAG = 'amdeliv_ch';

    /**
     * @var string[]
     */
    protected $_cacheTag = [self::CACHE_TAG, \Amasty\DeliveryDateManager\Model\ChannelSetResults::CACHE_TAG];

    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_deliverydate_deliverychannel';

    /**
     * @var ValidatorComposite
     */
    private $validatorComposite;

    public function __construct(
        Context $context,
        Registry $registry,
        ValidatorComposite $validatorComposite,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->validatorComposite = $validatorComposite;
    }

    protected function _construct()
    {
        $this->_init(DeliveryChannelResource::class);
    }

    /**
     * @return int
     */
    public function getChannelId(): int
    {
        return (int)$this->_getData(DeliveryChannelInterface::CHANNEL_ID);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string)$this->_getData(DeliveryChannelInterface::NAME);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->setData(DeliveryChannelInterface::NAME, $name);
    }

    /**
     * @param int|null $channelId
     *
     * @return void
     */
    public function setChannelId(?int $channelId): void
    {
        $this->setData(DeliveryChannelInterface::CHANNEL_ID, $channelId);
    }

    /**
     * @return int|null
     */
    public function getLimitId(): ?int
    {
        $data = $this->_getData(DeliveryChannelInterface::LIMIT_ID);
        if ($data === null) {
            return null;
        }

        return (int)$data;
    }

    /**
     * @param int|null $limitId
     *
     * @return void
     */
    public function setLimitId(?int $limitId): void
    {
        $this->setData(DeliveryChannelInterface::LIMIT_ID, $limitId);
    }

    /**
     * @return int
     */
    public function getHasOrderCounter(): int
    {
        return (int)$this->_getData(DeliveryChannelInterface::HAS_ORDER_COUNTER);
    }

    /**
     * @param int $hasOrderCounter
     *
     * @return void
     */
    public function setHasOrderCounter(int $hasOrderCounter): void
    {
        $this->setData(DeliveryChannelInterface::HAS_ORDER_COUNTER, $hasOrderCounter);
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return (int)$this->_getData(DeliveryChannelInterface::PRIORITY);
    }

    /**
     * @param int $priority
     *
     * @return void
     */
    public function setPriority(int $priority): void
    {
        $this->setData(DeliveryChannelInterface::PRIORITY, $priority);
    }

    /**
     * @return int|null
     */
    public function getConfigId(): ?int
    {
        $data = $this->_getData(DeliveryChannelInterface::CONFIG_ID);
        if ($data === null) {
            return null;
        }

        return (int)$data;
    }

    /**
     * @param int|null $configId
     */
    public function setConfigId(?int $configId): void
    {
        $this->setData(DeliveryChannelInterface::CONFIG_ID, $configId);
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return (bool)$this->getData(DeliveryChannelInterface::IS_ACTIVE);
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->setData(DeliveryChannelInterface::IS_ACTIVE, $isActive);
    }

    /**
     * Set flag to update only main entity
     */
    public function setSimpleUpdate(): void
    {
        $this->setData(DeliveryChannelResource::SKIP_HANDLERS, true);
    }

    protected function _getValidatorBeforeSave()
    {
        return $this->validatorComposite;
    }
}
