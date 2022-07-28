<?php

namespace Magenest\AbandonedCart\Helper;

use Magenest\AbandonedCart\Model\AbandonedCart;
use Magenest\AbandonedCart\Model\Mail\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Filesystem\Io\File;
use Magenest\AbandonedCart\Model\Config\Source\Mail as EmailStatus;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface as ScopeInterfaceAlias;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Catalog\Model\Product\Media\Config as CatalogConfig;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Email\Model\ResourceModel\Template as EmailTemplateResource;
use Magento\Email\Model\TemplateFactory as EmailTemplateModel;

class SendMail extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SEND_MAIL = 0;

    const SEND_TEST_MAIL = 1;
    /** @var TransportBuilder $_transportBuilder */
    protected $_transportBuilder;

    /** @var Data $_helperData */
    protected $_helperData;

    /** @var StateInterface $_inlineTranslation */
    protected $_inlineTranslation;

    protected $_vars = [];

    /** @var EncryptorInterface $_encryptor */
    protected $_encryptor;

    /** @var StoreManagerInterface $_storeManager */
    protected $_storeManager;

    /** @var  File $_file */
    protected $_file;

    /** @var DirectoryList */
    protected $_dir;

    /** @var CatalogConfig */
    protected $_config;

    /** @var LoggerInterface  */
    protected $_logger;

    /** @var Json  */
    protected $_json;

    protected $_templateModel;

    protected $_templateResource;

    /**
     * SendMail constructor.
     *
     * @param TransportBuilder $transportBuilder
     * @param Data $helperData
     * @param StateInterface $inlineTranslation
     * @param EncryptorInterface $encryptor
     * @param StoreManagerInterface $storeManager
     * @param File $file
     * @param DirectoryList $directoryList
     * @param CatalogConfig $catalogConfig
     * @param LoggerInterface $logger
     * @param Json $json
     * @param EmailTemplateModel $templateModel
     * @param EmailTemplateResource $templateResource
     * @param Context $context
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        Data $helperData,
        StateInterface $inlineTranslation,
        EncryptorInterface $encryptor,
        StoreManagerInterface $storeManager,
        File $file,
        DirectoryList $directoryList,
        CatalogConfig $catalogConfig,
        LoggerInterface $logger,
        Json $json,
        EmailTemplateModel $templateModel,
        EmailTemplateResource $templateResource,
        Context $context
    ) {
        $this->_transportBuilder = $transportBuilder;
        $this->_helperData = $helperData;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_encryptor = $encryptor;
        $this->_storeManager = $storeManager;
        $this->_file = $file;
        $this->_dir = $directoryList;
        $this->_config = $catalogConfig;
        $this->_logger = $logger;
        $this->_json = $json;
        $this->_templateModel = $templateModel;
        $this->_templateResource = $templateResource;
        parent::__construct($context);
    }

    public function send($abandonedCartLog)
    {
        try {
            $this->sendMail($abandonedCartLog);
            $log = 'Ok';
            $status = EmailStatus::STATUS_SENT;
            $now = new \DateTime();
            $date = $now->format(DateTime::DATETIME_PHP_FORMAT);
            $abandonedCartLog->setSendDate($date);
        } catch (\Exception $exception) {
            $log = $exception->getMessage();
            $status = EmailStatus::STATUS_FAILED;
        }
        if (!$abandonedCartLog->getSendTest()) {
            $abandonedCartLog->setStatus($status);
            $abandonedCartLog->setLog($log);
            $abandonedCartLog->save();
        }
    }

    protected function sendMail($abandonedCartLog)
    {
        $storeScope = ScopeInterfaceAlias::SCOPE_STORE;
        $from = $this->scopeConfig->getValue('abandonedcart/general/email_identity', $storeScope);
        $version = $this->_helperData->getVersionMagento();
        $emailTest = $abandonedCartLog->getData('send_test');
        if ($emailTest == null) {
            $decode = htmlspecialchars_decode($abandonedCartLog->getContent());
        } else {
            $content = htmlspecialchars_decode($abandonedCartLog->getContent());
            $decode = $this->getTextBetweenTags($content, "a");
        }
        $this->_transportBuilder->setMessageContent(
            $decode,
            $abandonedCartLog->getSubject(),
            $from,
            $abandonedCartLog->getRecipientAdress(),
            $abandonedCartLog->getRecipientName(),
            $version
        );


        $templateMail = $this->_templateModel->create();
        $this->_templateResource->load($templateMail, $abandonedCartLog->getTemplateId(), "template_id");

        $this->_transportBuilder->setTemplateIdentifier($templateMail->getOrigTemplateCode());
        $attachments = [];

        $attachedFiles = $this->_json->unserialize($abandonedCartLog->getData('attachments'));
        if (is_array($attachedFiles) && !empty($attachedFiles)) {
            $mediaPath = $this->_dir->getPath('media');
            foreach ($attachedFiles as $attachFileTypes) {
                if (!is_array($attachFileTypes)) {
                    break;
                }
                if (!isset($attachFileTypes['file'])) {
                    continue;
                }
                $filepath = $mediaPath . '/' . $this->_config->getTmpMediaPath($attachFileTypes['file']);
                $body = $this->_file->read($filepath);
                if (!$body) {
                    $this->_logger->debug('Could not read attachment file for mail ' . $this->getId());
                    continue;
                }
                $attachments[] = [
                    'body' => $body,
                    'name' => $attachFileTypes['file'],
                    'label' => $attachFileTypes['label']
                ];
            }
        }
        $vars = $this->_json->unserialize($abandonedCartLog->getContextVars());
        $this->_inlineTranslation->suspend();
        if (version_compare($version, '2.2.0') < 0) {
            // clear previous data first.
            $this->_transportBuilder->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $abandonedCartLog->getStoreId(),
                ]
            )->setTemplateVars(
                $vars
            )->addTo(
                $abandonedCartLog->getRecipientAdress(),
                $abandonedCartLog->getRecipientName()
            );
        } else {
            try {
                $this->_transportBuilder->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $abandonedCartLog->getStoreId(),
                    ]
                )->setTemplateVars(
                    $vars
                )->setFromByScope(
                    $this->scopeConfig->getValue('abandonedcart/general/email_identity', $storeScope)
                )->addTo(
                    $abandonedCartLog->getRecipientAdress(),
                    $abandonedCartLog->getRecipientName()
                );
            } catch (MailException $e) {
                $this->_logger->critical($e->getMessage());
            }
        }

        if ($bccMail = $abandonedCartLog->getData('bcc_email')) {
            $this->_transportBuilder->addBcc($bccMail);
        }
        if ($attachments) {
            if ($this->_transportBuilder->getMessage() && method_exists($this->_transportBuilder->getMessage(), 'createAttachment')) {
                foreach ($attachments as $attachment) {
                    if ($attachment) {
                        $this->_transportBuilder->createAttachment($attachment);
                    }
                }
                $transport = $this->_transportBuilder->getTransport();
            } else {
                $transport = $this->_transportBuilder->getTransport();
                foreach ($attachments as $attachment) {
                    if ($attachment) {
                        $this->_transportBuilder->createAttachment($attachment, $transport);
                    }
                }
            }
        }
        if (!isset($transport)) {
            try {
                $transport = $this->_transportBuilder->getTransport();
            } catch (LocalizedException $e) {
                $this->_logger->critical($e->getMessage());
            }
        }
        try {
            if ($transport) {
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
            }
        } catch (\Exception $exception) {
            $this->_logger->critical($exception->getMessage());
        }
    }

    function getTextBetweenTags($string, $tagname) {
        $pattern = '/<'.$tagname.'[^>]*>/i';
        // remove new line
        return preg_replace($pattern, '<a href="#"></a>', preg_replace('/[\n\r]+/', '', $string));
    }
}
