<?php

namespace Magenest\GoogleTagManager\Observer;

class LayoutLoadBeforeObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magenest\GoogleTagManager\Model\Bootstrap $moduleBootstrap
     */
    private $moduleBootstrap;

    /**
     * @var \Magenest\GoogleTagManager\Helper\Data
     */
    private $dataHelper;

    public function __construct(
        \Magenest\GoogleTagManager\Model\Bootstrap $moduleBootstrap,
        \Magenest\GoogleTagManager\Helper\Data $dataHelper
    ) {
        $this->moduleBootstrap = $moduleBootstrap;
        $this->dataHelper = $dataHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->dataHelper->isEnabled()) {
            return;
        }

        $this->moduleBootstrap->bootstrapView(
            $observer->getData('full_action_name'),
            $observer->getData('layout')
        );
    }
}
