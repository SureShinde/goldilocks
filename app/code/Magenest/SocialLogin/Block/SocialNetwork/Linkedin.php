<?php
namespace Magenest\SocialLogin\Block\SocialNetwork;

use Magenest\SocialLogin\Model\Linkedin\Client;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Linkedin
 * @package Magenest\SocialLogin\Block\SocialNetwork
 */
class Linkedin extends Template
{
    /**
     * @var Client
     */
    protected $_clientLinkedin;

    /**
     * @param Client $clientLinkedin
     * @param Context $context
     */
    public function __construct(
        Client $clientLinkedin,
        Context $context
    ) {
        $this->_clientLinkedin = $clientLinkedin;
        parent::__construct($context);
    }
    /**
     * @return String
     */
    public function getButtonUrl()
    {
        return $this->_urlBuilder->getUrl("sociallogin/index/socialUrl",['social' => 'linkedin']);
    }
    /**
     * @return Bool
     */
    public function isLinkedinEnabled()
    {
        return $this->_clientLinkedin->isEnabled();
    }
}
