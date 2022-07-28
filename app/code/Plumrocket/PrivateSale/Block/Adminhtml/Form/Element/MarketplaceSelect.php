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
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Adminhtml\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Escaper;
use Plumrocket\Base\Model\IsModuleInMarketplace;

class MarketplaceSelect extends Select
{
    /**
     * @var \Plumrocket\Base\Model\IsModuleInMarketplace
     */
    private $isModuleInMarketplace;

    /**
     * MarketplaceSelect constructor.
     *
     * @param Factory                                      $factoryElement
     * @param CollectionFactory                            $factoryCollection
     * @param Escaper                                      $escaper
     * @param \Plumrocket\Base\Model\IsModuleInMarketplace $isModuleInMarketplace
     * @param array                                        $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        IsModuleInMarketplace $isModuleInMarketplace,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->isModuleInMarketplace = $isModuleInMarketplace;
    }

    /**
     * @return string|null
     */
    public function getComment()
    {
        $comment = $this->getData('comment');

        if (! $comment) {
            return null;
        }

        $note = $this->isModuleInMarketplace->execute('PrivateSale')
            ? ''
            : 'Please note: you can display popup login & registration if you have <a target="_blank"
 href="https://store.plumrocket.com/popup-login-magento2-extension.html">Plumrocket Popup Login extension</a>
 installed.';

        return sprintf($comment->getText(), $note);
    }
}
