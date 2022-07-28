<?php

namespace Magenest\GoogleTagManager\Model\Customer;

class Context
{
    const STATUS_NOT_LOGGED_IN = 'NOT_LOGGED_IN';
    const STATUS_LOGGED_IN = 'LOGGED_IN';

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->httpContext = $httpContext;
    }

    public function getGroupCode()
    {
        if ((bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)) {
            return self::STATUS_LOGGED_IN;
        }

        return self::STATUS_NOT_LOGGED_IN;
    }
}
