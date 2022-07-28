<?php
namespace Magenest\SocialLogin\Block\SocialNetwork;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magenest\SocialLogin\Model\Zalo\Client;

/**
 * Class Zalo
 *
 * @package Magenest\SocialLogin\Block\SocialNetwork
 */
class Zalo extends Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magenest\SocialLogin\Model\Zalo\Client
     */
    protected $client;

    /**
     * Zalo constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magenest\SocialLogin\Model\Zalo\Client            $client
     * @param \Magento\Framework\View\Element\Template\Context   $context
     * @param array                                              $data
     */
    public function __construct
    (
        ScopeConfigInterface $scopeConfig,
        Client $client,
        Template\Context $context,
        array $data = []
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->client = $client;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function isZaloEnable()
    {
        return $this->client->isEnabled();
    }

    /**
     * @return string|void
     */
    public function getButtonUrl()
    {
        return $this->_urlBuilder->getUrl("sociallogin/index/socialUrl",['social' => 'zalo']);
    }

    /**
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }
}
