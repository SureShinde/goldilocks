<?php

namespace Magenest\Pandago\Model\Service;

use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Comment as ShipmentCommentResource;

class CommentService
{
    /**
     * @var Shipment\CommentFactory
     */
    protected $shipmentCommentFactory;

    /**
     * @var ShipmentCommentResource
     */
    protected $shipmentCommentResource;

    /**
     * @var ShipmentRepositoryInterface
     */
    protected $shipmentRepository;

    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $orderStatusHistoryRepository;

    /**
     * CommentService constructor.
     * @param OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param Shipment\CommentFactory $shipmentCommentFactory
     * @param ShipmentCommentResource $shipmentCommentResource
     */
    public function __construct(
        OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        \Magento\Sales\Model\Order\Shipment\CommentFactory $shipmentCommentFactory,
        ShipmentCommentResource $shipmentCommentResource
    ) {
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
        $this->shipmentRepository           = $shipmentRepository;
        $this->shipmentCommentFactory       = $shipmentCommentFactory;
        $this->shipmentCommentResource      = $shipmentCommentResource;
    }

    /**
     * @param Order $order
     * @param string| OrderStatusHistoryInterface $comment
     * @param false $notify
     * @param false $visibleOnFront
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function addOrderComment($order, $comment, $notify = false, $visibleOnFront = false)
    {
        if (!$comment instanceof OrderStatusHistoryInterface) {
            $comment = $order->addCommentToStatusHistory($comment, false, $visibleOnFront)
                ->setIsCustomerNotified($notify);
        }
        $this->orderStatusHistoryRepository->save($comment);
    }

    /**
     * @param Shipment|int $shipment
     * @param string $comment
     * @param bool $notify
     * @param bool $visibleOnFront
     * @return bool
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function addShipmentComment($shipment, $comment, $notify = false, $visibleOnFront = false)
    {
        if (!$shipment instanceof Shipment) {
            $shipment = $this->shipmentRepository->get($shipment);
        }
        if (!$comment instanceof \Magento\Sales\Model\Order\Shipment\Comment) {
            $comment = $this->shipmentCommentFactory->create()
                ->setComment($comment)
                ->setIsCustomerNotified($notify)
                ->setIsVisibleOnFront($visibleOnFront);
        }
        $comment->setShipment($shipment)
            ->setParentId($shipment->getId())
            ->setStoreId($shipment->getStoreId());

        $this->shipmentCommentResource->save($comment);
        return true;
    }
}
