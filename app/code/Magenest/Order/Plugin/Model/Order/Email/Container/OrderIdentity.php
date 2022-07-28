<?php

namespace Magenest\Order\Plugin\Model\Order\Email\Container;

use Magento\Framework\Registry;

class OrderIdentity
{
    private Registry $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Send notifications to store admin
     *
     * @param \Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject
     * @param $result
     * @return mixed
     */
    public function afterGetEmailCopyTo(
        \Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject,
        $result
    ) {
        $additionalEmail = $this->registry->registry('email_admin_store');
        $this->registry->unregister('email_admin_store');
        if ($additionalEmail) {
            $result = $result ? array_push($result, $additionalEmail) : [$additionalEmail];
        }
        return $result;
    }
}
