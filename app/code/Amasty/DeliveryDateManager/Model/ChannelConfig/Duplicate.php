<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\ChannelConfig;

use Amasty\DeliveryDateManager\Model\ModalDuplicateResolver\ResolverInterface;

class Duplicate implements ResolverInterface
{
    /**
     * @var Get
     */
    private $configGetter;

    /**
     * @var Save
     */
    private $configSaver;

    /**
     * @var ConfigDataFactory
     */
    private $configFactory;

    public function __construct(
        Get $configGetter,
        Save $configSaver,
        ConfigDataFactory $configFactory
    ) {
        $this->configGetter = $configGetter;
        $this->configSaver = $configSaver;
        $this->configFactory = $configFactory;
    }

    /**
     * @param int $configId
     * @return int
     */
    public function execute(int $configId): int
    {
        /** @var ConfigData $mainConfig */
        $mainConfig = $this->configGetter->execute($configId);

        /** @var ConfigData $newConfig */
        $newConfig = $this->configFactory->create();
        $newConfig->setData($mainConfig->getData());
        $newConfig->setConfigId(null);
        $newConfig->setName('Copy of ' . $mainConfig->getName());
        $newConfig = $this->configSaver->execute($newConfig);

        return $newConfig->getId();
    }
}
