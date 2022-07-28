<?php
namespace Magenest\PendingCod\Cron;

use Magenest\PendingCod\Model\PendingOrderFactory;
use Magenest\PendingCod\Model\ResourceModel\PendingOrder\CollectionFactory;
use Magenest\PendingCod\Model\ResourceModel\PendingOrderFactory as ResourceFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Truncate
 * @package Magenest\PendingCod\Cron
 */
class Truncate
{
    /**
     * @var ResourceFactory
     */
    protected $pendingOrderResourceFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var PendingOrderFactory
     */
    protected $pendingOrderFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;


    /**
     * Truncate constructor.
     * @param CollectionFactory $collectionFactory
     * @param PendingOrderFactory $pendingOrderFactory
     * @param LoggerInterface $logger
     * @param ResourceFactory $pendingOrderResourceFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        PendingOrderFactory $pendingOrderFactory,
        LoggerInterface $logger,
        ResourceFactory $pendingOrderResourceFactory
    ) {
        $this->pendingOrderResourceFactory = $pendingOrderResourceFactory;
        $this->collectionFactory = $collectionFactory;
        $this->pendingOrderFactory = $pendingOrderFactory;
        $this->_logger = $logger;
    }

    public function execute()
    {
        try {
            $collection = $this->collectionFactory->create();
            $emailSent = $collection->addFieldToFilter('email_sent',['eq' => 1]);
            $emailSent->walk('delete');
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return;
        }
    }
}
