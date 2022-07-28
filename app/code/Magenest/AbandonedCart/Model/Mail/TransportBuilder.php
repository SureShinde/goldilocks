<?php

namespace Magenest\AbandonedCart\Model\Mail;

use Magenest\AbandonedCart\Helper\Data;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\Exception\InvalidArgumentException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MessageInterfaceFactory;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;
use Zend\Mime\Mime;
use Zend\Mime\Part;

class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    protected $body;

    protected $subject;

    protected $from;

    protected $recipientAddress;
    protected $recipientName;

    /**
     * Template Identifier
     *
     * @var string
     */
    protected $templateIdentifier;

    /**
     * Template Model
     *
     * @var string
     */
    protected $templateModel;

    /**
     * Template Variables
     *
     * @var array
     */
    protected $templateVars;

    /**
     * Template Options
     *
     * @var array
     */
    protected $templateOptions;

    /**
     * Mail Transport
     *
     * @var TransportInterface
     */
    protected $transport;

    /**
     * Template Factory
     *
     * @var FactoryInterface
     */
    protected $templateFactory;

    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Message
     *
     * @var MessageInterface
     */
    protected $message;

    /**
     * Sender resolver
     *
     * @var SenderResolverInterface
     */
    protected $_senderResolver;

    /**
     * @var TransportInterfaceFactory
     */
    protected $mailTransportFactory;

    /**
     * Param that used for storing all message data until it will be used
     *
     * @var array
     */
    private $messageData = [];

    /**
     * @var EmailMessageInterfaceFactory
     */
    private $emailMessageInterfaceFactory;

    /**
     * @var MimeMessageInterfaceFactory
     */
    private $mimeMessageInterfaceFactory;

    /**
     * @var MimePartInterfaceFactory
     */
    private $mimePartInterfaceFactory;

    /**
     * @var AddressConverter|null
     */
    private $addressConverter;

    protected $version;

    /**
     * Add cc address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return \Magento\Framework\Mail\Template\TransportBuilder
     */
    public function addCc($address, $name = '')
    {
        $this->version = $this->version ? $this->version : $this->objectManager->get(Data::class)->getVersionMagento();
        if (version_compare($this->version, '2.3.0') < 0) {
            return parent::addCc($address, $name);
        }
        $this->addAddressByType('cc', $address, $name);

        return $this;
    }


    /**
     * Add to address
     *
     * @param array|string $address
     * @param string $name
     *
     * @return \Magento\Framework\Mail\Template\TransportBuilder
     * @throws InvalidArgumentException
     */
    public function addTo($address, $name = '')
    {
        $this->version = $this->version ? $this->version : $this->objectManager->get(Data::class)->getVersionMagento();
        if (version_compare($this->version, '2.3.0') < 0) {
            return parent::addTo($address, $name);
        }
        $this->addAddressByType('to', $address, $name);

        return $this;
    }

    /**
     * Add bcc address
     *
     * @param array|string $address
     *
     * @return \Magento\Framework\Mail\Template\TransportBuilder
     * @throws InvalidArgumentException
     */
    public function addBcc($address)
    {
        $this->version = $this->version ? $this->version : $this->objectManager->get(Data::class)->getVersionMagento();
        if (version_compare($this->version, '2.3.0') < 0) {
            return parent::addBcc($address);
        }
        $this->addAddressByType('bcc', $address);

        return $this;
    }

    /**
     * Set Reply-To Header
     *
     * @param string $email
     * @param string|null $name
     *
     * @return \Magento\Framework\Mail\Template\TransportBuilder
     * @throws InvalidArgumentException
     */
    public function setReplyTo($email, $name = null)
    {
        $this->version = $this->version ? $this->version : $this->objectManager->get(Data::class)->getVersionMagento();
        if (version_compare($this->version, '2.3.0') < 0) {
            return parent::setReplyTo($email, $name);
        }
        $this->addAddressByType('replyTo', $email, $name);

        return $this;
    }

    /**
     * Set mail from address by scopeId
     *
     * @param string|array $from
     * @param string|int $scopeId
     *
     * @return \Magento\Framework\Mail\Template\TransportBuilder
     * @throws InvalidArgumentException
     * @throws MailException
     * @since 102.0.1
     */
    public function setFromByScope($from, $scopeId = null)
    {
        $this->version = $this->version ? $this->version : $this->objectManager->get(Data::class)->getVersionMagento();
        if (version_compare($this->version, '2.3.0') < 0) {
            return parent::setFromByScope($from, $scopeId);
        }
        $result = $this->_senderResolver->resolve($from, $scopeId);
        $this->addAddressByType('from', $result['email'], $result['name']);

        return $this;
    }

    /**
     * @return \Magento\Framework\Mail\MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return TransportBuilder
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        return $this->prepareMessage();
    }

    /**
     * @return $this
     */
    public function clearFrom()
    {
        //$this->_from = null;
        $this->message->clearFrom('From');
        return $this;
    }

    /**
     * @return $this
     */
    public function clearSubject()
    {
        $this->message->clearSubject();
        return $this;
    }

    /**
     * @return $this
     */
    public function clearMessageId()
    {
        $this->message->clearMessageId();
        return $this;
    }

    /**
     * @return $this
     */
    public function clearBody()
    {
        $this->message->setParts([]);
        return $this;
    }

    /**
     * @return $this
     */
    public function clearRecipients()
    {
        $this->message->clearRecipients();
        return $this;
    }

    /**
     * @param $headerName
     *
     * @return $this
     */
    public function clearHeader($headerName)
    {
        if (isset($this->_headers[$headerName])) {
            unset($this->_headers[$headerName]);
        }
        return $this;
    }

    /**
     * @param $body
     * @param $subject
     * @param $from
     * @param $recipientAddress
     * @param $recipientName
     * @param $version
     */
    public function setMessageContent($body, $subject, $from, $recipientAddress, $recipientName, $version)
    {
        $this->body = $body;
        $this->subject = $subject;
        $this->from = $from;
        $this->recipientAddress = $recipientAddress;
        $this->recipientName = $recipientName;
        $this->version  = $version;
    }

    /**
     * Prepare message.
     *
     * @return \Magento\Framework\Mail\Template\TransportBuilder
     * @throws LocalizedException if template type is unknown
     */
    protected function prepareMessage()
    {
        $this->version = $this->version ? $this->version : $this->objectManager->get(Data::class)->getVersionMagento();
        if (version_compare($this->version, '2.3.0') < 0) {
            if ($this->from) {
                $from = $this->_senderResolver->resolve(
                    $this->from
                );
                $this->message->setMessageType('text/html')->setBody($this->body)->setSubject($this->subject, ENT_QUOTES)->setFrom($from['email'], $from['name']);
            } else {
                $this->message->setMessageType('text/html')->setBody($this->body)->setSubject($this->subject, ENT_QUOTES);
            }
        } else {
            $this->emailMessageInterfaceFactory = $this->objectManager->get(EmailMessageInterfaceFactory::class);
            $this->mimeMessageInterfaceFactory = $this->objectManager->get(MimeMessageInterfaceFactory::class);
            $this->mimePartInterfaceFactory = $this->objectManager->get(MimePartInterfaceFactory::class);
            $template = $this->getTemplate();
            $content = $this->body ? $this->body : $template->processTemplate();

            if ($this->from) {
                $from = $this->_senderResolver->resolve(
                    $this->from
                );
                $this->setFromByScope($from);
            }
            if ($this->recipientAddress && $this->recipientName) {
                $this->addTo($this->recipientAddress, $this->recipientName);
            }
            switch ($template->getType()) {
                case TemplateTypesInterface::TYPE_TEXT:
                    $part['type'] = MimeInterface::TYPE_TEXT;
                    break;

                case TemplateTypesInterface::TYPE_HTML:
                    $part['type'] = MimeInterface::TYPE_HTML;
                    break;

                default:
                    throw new LocalizedException(
                        new Phrase('Unknown template type')
                    );
            }
            /** @var \Magento\Framework\Mail\MimePartInterface $mimePart */
            $mimePart = $this->mimePartInterfaceFactory->create(['content' => $content]);
            $this->messageData['encoding'] = $mimePart->getCharset();
            $this->messageData['body'] = $this->mimeMessageInterfaceFactory->create(
                ['parts' => [$mimePart]]
            );

            $this->messageData['subject'] = html_entity_decode(
                (string)$this->subject ? $this->subject : $template->getSubject(),
                ENT_QUOTES
            );

            $this->message = $this->emailMessageInterfaceFactory->create($this->messageData);
        }

        return $this;
    }

    /**
     * @param $params
     * @param bool $transport
     * @return $this
     */
    public function createAttachment($params, $transport = false)
    {
        $type = isset($params['cat']) ? $params['cat'] : \Zend_Mime::TYPE_OCTETSTREAM;
        if ($transport === false) {
            if ($type == 'pdf') {
                $this->message->createAttachment(
                    $params['body'],
                    'application/pdf',
                    \Zend_Mime::DISPOSITION_ATTACHMENT,
                    \Zend_Mime::ENCODING_BASE64,
                    $params['name']
                );
            } elseif ($type == 'png') {
                $this->message->createAttachment(
                    $params['body'],
                    'image/png',
                    \Zend_Mime::DISPOSITION_ATTACHMENT,
                    \Zend_Mime::ENCODING_BASE64,
                    $params['name']
                );
            } else {
                $encoding = isset($params['encoding']) ? $params['encoding'] : \Zend_Mime::ENCODING_BASE64;
                $this->message->createAttachment(
                    $params['body'],
                    $type,
                    \Zend_Mime::DISPOSITION_ATTACHMENT,
                    $encoding,
                    $params['name']
                );
            }
        } else {
            $this->addAttachment($params, $transport);
        }
        return $this;
    }

    /**
     * @param $params
     * @param $transport
     * @throws \Exception
     */
    public function addAttachment($params, $transport)
    {
        $zendPart = $this->createZendMimePart($params);
        $parts = $transport->getMessage()->getBody()->addPart($zendPart);
        $transport->getMessage()->setBody($parts);
    }

    /**
     * @param $params
     * @return Part
     * @throws \Exception
     */
    protected function createZendMimePart($params)
    {
        if (class_exists('Zend\Mime\Mime') && class_exists('Zend\Mime\Part')) {
            $type = isset($params['type']) ? $params['type'] : Mime::TYPE_OCTETSTREAM;
            $part = new Part($params['body']);
            $part->type = $type;
            $part->filename = $params['name'];
            $part->disposition = Mime::DISPOSITION_ATTACHMENT;
            $part->encoding = Mime::ENCODING_BASE64;
            return $part;
        } else {
            throw new \Exception("Missing Zend Framework Source");
        }
    }

    /**
     * @return \Magento\Framework\Mail\Template\TransportBuilder
     */
    public function reset()
    {
        return parent::reset(); // TODO: Change the autogenerated stub
    }

    /**
     * Handles possible incoming types of email (string or array)
     *
     * @param string $addressType
     * @param string|array $email
     * @param string|null $name
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function addAddressByType(string $addressType, $email, ?string $name = null): void
    {
        $this->addressConverter = $this->objectManager->get(AddressConverter::class);
        if (is_string($email)) {
            $this->messageData[$addressType][] = $this->addressConverter->convert($email, $name);
            return;
        }
        $convertedAddressArray = $this->addressConverter->convertMany($email);
        if (isset($this->messageData[$addressType])) {
            $this->messageData[$addressType] = array_merge(
                $this->messageData[$addressType],
                $convertedAddressArray
            );
        } else {
            $this->messageData[$addressType] = $convertedAddressArray;
        }
    }
}
