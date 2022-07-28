<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Controller\Promo\Banner;

use Magento\Cms\Block\Block as CmsBlock;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Plumrocket\PrivateSale\Model\Promo\Banner\Variables;
use Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory as EventCollectionFactory;

class Load extends Action
{
    /**
     * @var EventCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var \Plumrocket\PrivateSale\Model\Promo\Banner\Variables
     */
    private $promoBannerVariables;

    /**
     * @param \Magento\Framework\App\Action\Context                               $context
     * @param \Plumrocket\PrivateSale\Model\ResourceModel\Event\CollectionFactory $collectionFactory
     * @param \Magento\Framework\View\Result\PageFactory                          $pageFactory
     * @param \Plumrocket\PrivateSale\Model\Promo\Banner\Variables                $promoBannerVariables
     */
    public function __construct(
        Context $context,
        EventCollectionFactory $collectionFactory,
        PageFactory $pageFactory,
        Variables $promoBannerVariables
    ) {
        parent::__construct($context);

        $this->pageFactory = $pageFactory;
        $this->collectionFactory = $collectionFactory;
        $this->promoBannerVariables = $promoBannerVariables;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if ($this->getRequest()->isXmlHttpRequest()) {
            $position = $this->getRequest()->getParam('position');

            $result->setData(
                [
                    'success' => true,
                    'data'    => $this->getBannerContent($position),
                ]
            );
        }

        return $result;
    }

    /**
     * @param $position
     * @return array
     */
    private function getBannerContent($position): array
    {
        /** @var \Plumrocket\PrivateSale\Model\ResourceModel\Event\Collection $events */
        $events = $this->collectionFactory->create()
            ->addAttributeToSelect('*')
            ->addActiveFilters()
            ->addFieldToFilter('enable_banner', ['eq' => 1])
            ->addFieldToFilter('banner_position', ['eq' => $position]);

        $content = [];

        foreach ($events as $event) {
            if ($blockId = $event->getData('banner_template')) {
                /** @var \Magento\Cms\Block\Block $cmsBlock */
                $cmsBlock = $this->pageFactory->create()->getLayout()->createBlock(CmsBlock::class);
                $cmsBlock->setBlockId($blockId);

                $content[] = [
                    'content' => $this->promoBannerVariables->apply($event, $cmsBlock->toHtml())
                ];
            }
        }

        return $content;
    }
}
