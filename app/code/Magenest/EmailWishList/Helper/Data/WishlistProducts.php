<?php

namespace Magenest\EmailWishList\Helper\Data;

use Magenest\AbandonedCart\Model\LogContentFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface;

class WishlistProducts
{
    const XML_PATH_TIME_EMAIL_WISHLIST = 'magenest_email_wishlist/setting/auto_send_email_wishlist';
    const XML_PATH_EMAIL_SENDER = 'magenest_email_wishlist/setting/email_sender_wishlist';
    const TABLE_NAME = 'magenest_abacar_log';
    const TEMPLATE_EMAIL_WISHLIST = 'magenest_email_wishlist_setting_email_wishlist';

    /**
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $wishlist;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StateInterface
     */
    private StateInterface $inlineTranslation;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    private LogContentFactory $contentFactory;


    /**
     * @param \Magento\Wishlist\Model\Wishlist $wishlist
     * @param \Magento\Framework\App\Helper\Context $context
     * @param StateInterface $inlineTranslation
     * @param Escaper $escaper
     * @param TransportBuilder $transportBuilder
     * @param LoggerInterface $logger
     * @param DateTime\DateTime $date
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Wishlist\Model\Wishlist            $wishlist,
        \Magento\Framework\App\Helper\Context       $context,
        StateInterface                              $inlineTranslation,
        Escaper                                     $escaper,
        TransportBuilder                            $transportBuilder,
        LoggerInterface                             $logger,
        LogContentFactory                           $contentFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\ResourceConnection   $resource
    )
    {
        $this->scopeConfig = $context->getScopeConfig();
        $this->date = $date;
        $this->resource = $resource;
        $this->wishlist = $wishlist;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->contentFactory = $contentFactory;
    }

    /**
     * @return void
     */
    public function checkUpdateWishList()
    {
        $bulkInsert = [];
        $timeSend = $this->scopeConfig->getValue(self::XML_PATH_TIME_EMAIL_WISHLIST); // get time to send to customer
        $emailSender = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_SENDER);
        $dateCurrently = $this->date->gmtDate();
        $dateCompare = date('Y-m-d H:i:s', strtotime($dateCurrently) - (int)$timeSend * 60 * 60 );
        $connection = $this->resource->getConnection();
        $select = $connection->select() // get customer have wishlist item not update on the time, which configured in admin
        ->from(
            ['wl' => 'wishlist']
        )
            ->join(
                ['wlt' => 'wishlist_item'],
                'wlt.wishlist_id = wl.wishlist_id'
            )->join(
                ['cus' => 'customer_entity'],
                'wl.customer_id = cus.entity_id', ['email', 'firstname', 'lastname']
            )->where('wl.updated_at  < ? ', $dateCompare)->group('wlt.wishlist_id');

        $collection = $connection->fetchAll($select);
        foreach ($collection as $data) {
            $this->sendEmail($emailSender, $data['email'], $data['product_id']);
            $bulkInsert[] = [
                'rule_id' => '0',
                'type' => 'Email',
                'recipient_adress' => $data['email'],
                'recipient_name' => $data['firstname'] . ' ' . $data['lastname'],
                'status' => '2'
            ];
        }
        $this->insertMultiple(self::TABLE_NAME, $bulkInsert);
    }

    /**
     * @param $table
     * @param $data
     * @return int
     * @throws \Exception
     */
    public function insertMultiple($table, $data)
    {
        try {
            $tableName = $this->resource->getTableName($table);
            return $this->resource->getConnection()->insertMultiple($tableName, $data);
        } catch (\Exception $e) {
            throw new \Exception('Error in process saving data' . $e->getMessage());
        }
    }

    /**
     * @param $emailSender
     * @param $emailToSend
     * @param $productId
     * @return void
     */
    public function sendEmail($emailSender, $emailToSend, $productId)
    {

        $this->inlineTranslation->suspend();
        try {
            $sender = [
                'name' => $this->escaper->escapeHtml('Admin'),
                'email' => $this->escaper->escapeHtml($emailSender),
            ];
            $transport = $this->transportBuilder
                ->setTemplateIdentifier(self::TEMPLATE_EMAIL_WISHLIST)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(['productId' => $productId])
                ->setFromByScope($sender)
                ->addTo($emailToSend)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            return;
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->logger->error($e->getMessage());
            return;
        }
    }
}
