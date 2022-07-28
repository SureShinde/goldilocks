<?php

namespace Magenest\AbandonedCart\Controller\Track;

use Magenest\AbandonedCart\Model\Config\Source\Mail as EmailStatus;
use Magenest\AbandonedCart\Model\Cron;
use Magenest\AbandonedCart\Model\LogContent;
use Magenest\AbandonedCart\Model\LogContentFactory;
use Magenest\AbandonedCart\Model\Rule;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Encryption\EncryptorInterface as EncryptorInterface;
use Magenest\AbandonedCart\Model\ResourceModel\LogContent\CollectionFactory as LogCollection;
use Magenest\AbandonedCart\Model\ResourceModel\LogContent as LogResource;
use Magenest\AbandonedCart\Model\RuleFactory as RuleModel;
use Magenest\AbandonedCart\Model\ResourceModel\Rule as RuleResource;
use Magento\Framework\Serialize\Serializer\Json;

class Click extends \Magenest\AbandonedCart\Controller\Track
{
    /** @var EncryptorInterface $_encryptor */
    protected $_encryptor;

    /** @var LogContentFactory $_logContentFactory */
    protected $_logContentFactory;

    /** @var LogCollection */
    protected $_logCollection;

    /** @var LogResource */
    protected $_logResource;

    /** @var RuleModel */
    protected $_ruleModel;

    /** @var RuleResource */
    protected $_ruleResource;

    /** @var Json  */
    protected $_json;

    /**
     * Click constructor.
     *
     * @param EncryptorInterface $encryptor
     * @param LogContentFactory $logContentFactory
     * @param Context $context
     * @param Session $checkoutSession
     * @param CustomerSession $customerSession
     * @param LogCollection $logCollection
     * @param LogResource $logResource
     * @param RuleModel $ruleModel
     * @param RuleResource $ruleResource
     * @param Json $json
     */
    public function __construct(
        EncryptorInterface $encryptor,
        LogContentFactory $logContentFactory,
        Context $context,
        Session $checkoutSession,
        CustomerSession $customerSession,
        LogCollection $logCollection,
        LogResource $logResource,
        RuleModel $ruleModel,
        RuleResource $ruleResource,
        Json $json
    ) {
        $this->_encryptor = $encryptor;
        $this->_logContentFactory = $logContentFactory;
        $this->_logCollection = $logCollection;
        $this->_logResource = $logResource;
        $this->_ruleModel = $ruleModel;
        $this->_ruleResource = $ruleResource;
        $this->_json = $json;
        parent::__construct($context, $checkoutSession, $customerSession);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $id = $this->_encryptor->decrypt(Cron::base64UrlDecode($id));

            /** @var LogContent $mail */
            $mail = $this->_logContentFactory->create();
            $this->_logResource->load($mail, $id, 'id');
            if ($mail->getId()) {
                try {
                    $mail->setData('clicks', 1);
                    $mail->setData('opened', 1);
                    // check cancel conditions send email
                    $ruleId = $mail->getData('rule_id');
                    /** @var Rule $ruleModel */
                    $ruleModel = $this->_ruleModel->create();
                    $this->_ruleResource->load($ruleModel, $ruleId, 'id');
                    $this->_logResource->save($mail);
                    if ($cancel = $ruleModel->getData('cancel_rule_when')) {
                        $cancel_rule_when = $this->_json->unserialize($cancel);
                        if (in_array(3, $cancel_rule_when)) {
                            $collections = $this->_logCollection->create()
                                ->addFieldToFilter('rule_id', $ruleId)
                                ->addFieldToFilter('abandonedcart_id', $mail->getData('abandonedcart_id'))
                                ->addFieldToFilter('status', EmailStatus::STATUS_QUEUED);
                            /** @var LogContent $collection */
                            foreach ($collections as $collection) {
                                $opened = $collection->getData('opened');
                                if ($opened == 0 || $opened == '') {
                                    $collection->addData([
                                        'status' => EmailStatus::STATUS_CANCELLED,
                                        'log' => 'Link from Email Clicked'
                                    ]);
                                } else {
                                    $collection->addData([
                                        'status' => EmailStatus::STATUS_CANCELLED,
                                        'log' => 'Link from Email Clicked'
                                    ]);
                                }
                                $this->_logResource->save($collection);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($url = $this->getRequest()->getParam('des')) {
            $url = Cron::base64UrlDecode($url);
            $resultRedirect->setUrl($url);
        } else {
            $resultRedirect->setPath('/');
        }
        return $resultRedirect;
    }
}
