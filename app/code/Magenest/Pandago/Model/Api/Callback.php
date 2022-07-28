<?php

namespace Magenest\Pandago\Model\Api;

use Magenest\Pandago\Model\Service\CommentService;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\Data\ShipmentTrackInterface;

class Callback implements \Magenest\Pandago\Api\CallbackInterface
{
    const MAPPING_STATUS = [
        'NEW' => 'Order has been created',
        'RECEIVED' => "We've accepted the order and will be assigning it to a courier",
        'WAITING_FOR_TRANSPORT'	=> 'Looking for a courier to pick up and deliver the order',
        'ASSIGNED_TO_TRANSPORT' =>	'Assigning order to a courier',
        'COURIER_ACCEPTED_DELIVERY' =>	'Courier accepted to pick up and deliver the order',
        'NEAR_VENDOR' => 'Courier is near the pick-up point',
        'PICKED_UP'	=> 'Courier has picked up the order',
        'COURIER_LEFT_VENDOR' => 'Courier has left from pick-up point',
        'NEAR_CUSTOMER' => 'Courier is near the drop-off point',
        'DELIVERED' => 'Courier has delivered the order',
        'DELAYED' => 'Order delivery has been delayed and estimated delivery time has been updated',
        'CANCELLED' => 'Order has been cancelled'  ,
    ];

    const MAPPING_STATUS_CODE = [
        'NEW' => 2,
        'RECEIVED' => 3,
        'WAITING_FOR_TRANSPORT'	=> 4,
        'ASSIGNED_TO_TRANSPORT' =>	5,
        'COURIER_ACCEPTED_DELIVERY' =>	6,
        'NEAR_VENDOR' => 7,
        'PICKED_UP'	=> 8,
        'COURIER_LEFT_VENDOR' => 9,
        'NEAR_CUSTOMER' => 10,
        'DELIVERED' => 11,
        'DELAYED' => 12,
        'CANCELLED' => 13,
    ];
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Json
     */
    protected $serializer;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    private CommentService $commentService;

    private CollectionFactory $collectionFactory;
    /**
     * @var ShipmentRepositoryInterface
     */
    private ShipmentRepositoryInterface $shipmentRepository;

    /**
     * Callback constructor.
     * @param RequestInterface $request
     * @param Json $serializer
     * @param CollectionFactory $collectionFactory
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param LoggerInterface $logger
     * @param CommentService $commentService
     */
    public function __construct(
        RequestInterface $request,
        Json $serializer,
        CollectionFactory $collectionFactory,
        ShipmentRepositoryInterface $shipmentRepository,
        LoggerInterface $logger,
        CommentService $commentService
    ) {
        $this->request = $request;
        $this->serializer = $serializer;
        $this->collectionFactory = $collectionFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->logger = $logger;
        $this->commentService = $commentService;
    }

    /**
     * Execute callback.
     *
     * @return array
     */
    public function execute()
    {
        $data = $this->request->getContent();
        $this->logger->info('Pandago Callback request:' . $data);
        if ($data) {
            $callbackData = $this->serializer->unserialize($data);
            $this->updateData($callbackData);
        }
        return [];
    }

    /**
     * @param $data
     */
    public function updateData($data)
    {
        try {
            $statusCode = $data['status'] ?? false;
            $pandagoId = $data['order_id'] ?? false;
            $trackingLink = $data['tracking_link'] ?? false;
            if ($pandagoId) {
                $tracking = $this->collectionFactory->create()
                    ->addFieldToFilter(ShipmentTrackInterface::TRACK_NUMBER, $pandagoId)
                    ->setPageSize(1)
                    ->getFirstItem();
                /** @var Shipment $shipment */
                $shipment = $tracking->getShipment();
                $comment = self::MAPPING_STATUS[$statusCode] ?? $statusCode;
                $statusId = self::MAPPING_STATUS_CODE[$statusCode] ?? false;
                if ($shipmentId = $shipment->getId()) {
                    if ($trackingLink) {
                        $tracking->addData(['description' => $trackingLink]);
                        $shipment->addTrack($tracking);
                    }
                    if ($statusId) {
                        $shipment->setShipmentStatus($statusId);
                        $this->shipmentRepository->save($shipment);
                    }
                    $this->commentService->addShipmentComment($shipmentId, $comment);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->critical(__('Pandago callback exception:' . $exception->getMessage()));
        }
    }
}
