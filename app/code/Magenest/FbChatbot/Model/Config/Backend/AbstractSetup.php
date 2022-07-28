<?php
namespace Magenest\FbChatbot\Model\Config\Backend;

use Magenest\FbChatbot\Model\Bot;
use Magenest\FbChatbot\Api\MenuRepositoryInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

abstract class AbstractSetup extends \Magento\Framework\App\Config\Value{

    /**
     * @var Bot
     */
    protected $bot;

    /**
     * @var MenuRepositoryInterface
     */
    protected $menuRepository;

    /**
     * AbstractSetup constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Bot $bot
     * @param MenuRepositoryInterface $menuRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Bot $bot,
        MenuRepositoryInterface $menuRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->bot = $bot;
        $this->menuRepository = $menuRepository;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

}
