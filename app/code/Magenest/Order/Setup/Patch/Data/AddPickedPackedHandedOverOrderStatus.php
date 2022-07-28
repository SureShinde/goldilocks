<?php
namespace Magenest\Order\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;
use Psr\Log\LoggerInterface;

class AddPickedPackedHandedOverOrderStatus implements DataPatchInterface
{
    const STATUS_CODE_PACKED = 'packed';
    const STATUS_CODE_PICKED = 'picked';
    const STATUS_CODE_HANDED = 'handed_over';

    /**
     * @var array|array[]
     */
    protected array $_status = [
        self::STATUS_CODE_PACKED => [
            'label' => 'Packed',
            'state' => Order::STATE_PROCESSING
        ],
        self::STATUS_CODE_PICKED => [
            'label' => 'Picked',
            'state' => Order::STATE_PROCESSING
        ],
        self::STATUS_CODE_HANDED => [
            'label' => 'Handed Over',
            'state' => Order::STATE_PROCESSING
        ]
    ];

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var StatusFactory
     */
    protected $statusFactory;

    /**
     * @var StatusResourceFactory
     */
    protected $statusResourceFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * AddPickedPackedHandedOverOrderStatus constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param StatusFactory $statusFactory
     * @param LoggerInterface $logger
     * @param StatusResourceFactory $statusResourceFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        StatusFactory $statusFactory,
        LoggerInterface $logger,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->statusFactory = $statusFactory;
        $this->statusResourceFactory = $statusResourceFactory;
        $this->_logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $statusFactory = $this->statusFactory->create();
        $statusResource = $this->statusResourceFactory->create();
        foreach ($this->_status as $code => $item) {
            $statusFactory->setData(
                [
                    'status' => $code,
                    'label' => $item['label']
                ]);
            try {
                $statusResource->save($statusFactory);
                $statusFactory->assignState($item['state'], false, true);
            } catch (\Exception $exception) {
                $this->_logger->error($exception->getMessage());
                return $this;
            }
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
