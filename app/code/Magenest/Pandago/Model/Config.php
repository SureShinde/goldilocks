<?php

namespace Magenest\Pandago\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * Config general path constants
     */
    const XML_PATH_ENABLED = 'carriers/pandago/active';
    const XML_PATH_ENABLE_LOGGING = 'carriers/pandago/enable_logging';
    const XML_PATH_CLIENT_ID = 'carriers/pandago/client_id';
    const XML_PATH_KEY_ID = 'carriers/pandago/key_id';
    const XML_PATH_SCOPE = 'carriers/pandago/scope';
    const XML_PATH_AUD = 'carriers/pandago/aud';
    const XML_SANDBOX_MODE = 'carriers/pandago/sandbox_mode';
    const XML_PATH_CALLBACK_URI = 'carriers/pandago/callback_uri';
    const XML_PATH_SANDBOX_FILE_PEM = 'carriers/pandago/sandbox_file_pem';
    const XML_PATH_DESCRIPTION = 'carriers/pandago/description';

    /**
     * Config endpoint path constants
     */
    const XML_PATH_SANDBOX_ENDPOINT = 'carriers/pandago/sandbox_host';
    const XML_PATH_PRODUCTION_ENDPOINT = 'carriers/pandago/production_host';
    const XML_PATH_SANDBOX_ENDPOINT_ORDER = 'carriers/pandago/sandbox_host_order';
    const XML_PATH_PRODUCTION_ENDPOINT_ORDER = 'carriers/pandago/production_host_order';
    const XML_PATH_PRODUCTION_ENDPOINT_BASE = 'carriers/pandago/production_host_base';
    const XML_PATH_SANDBOX_ENDPOINT_BASE = 'carriers/pandago/sandbox_host_base';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;
    private EncryptorInterface $encryptor;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->encryptor = $encryptor;
    }

    /**
     * Is enabled integration
     *
     * @param null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get client id
     *
     * @param null $storeId
     * @return string
     */
    public function getClientId($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CLIENT_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get key id
     *
     * @param null $storeId
     * @return string
     */
    public function getKeyId($storeId = null): string
    {
        $key = (string)$this->scopeConfig->getValue(
            self::XML_PATH_KEY_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($key) {
            $key = $this->encryptor->decrypt($key);
        }
        return $key;
    }

    /**
     * Get secret file pem
     *
     * @param null $storeId
     * @return string
     */
    public function getSecretFilePem($storeId = null): string
    {
        $isSandbox = $this->getSandboxMode();
        return (string)$this->scopeConfig->getValue(
            $isSandbox ? self::XML_PATH_SANDBOX_FILE_PEM : self::XML_PATH_PRODUCTION_ENDPOINT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get scope
     *
     * @param null $storeId
     * @return string
     */
    public function getScope($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_SCOPE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Aud
     *
     * @param null $storeId
     * @return string
     */
    public function getAud($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_AUD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get endpoint url
     *
     * @param null $storeId
     * @return string
     */
    public function getEndpointUrl($storeId = null): string
    {
        $isSandbox = $this->getSandboxMode();
        return (string)$this->scopeConfig->getValue(
            $isSandbox ? self::XML_PATH_SANDBOX_ENDPOINT : self::XML_PATH_PRODUCTION_ENDPOINT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get endpoint base url
     *
     * @param null $storeId
     * @return string
     */
    public function getEndpointBaseUrl($storeId = null): string
    {
        $isSandbox = $this->getSandboxMode();
        return (string)$this->scopeConfig->getValue(
            $isSandbox ? self::XML_PATH_SANDBOX_ENDPOINT_BASE : self::XML_PATH_PRODUCTION_ENDPOINT_BASE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Description
     *
     * @param null $storeId
     * @return string
     */
    public function getDescription($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get warehouse ID
     *
     * @param null $storeId
     * @return bool
     */
    public function getSandboxMode($storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_SANDBOX_MODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get enable_logging
     *
     * @param null $storeId
     * @return string
     */
    public function getEnableLogging($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_LOGGING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
