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
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Adminhtml\Form\Field;

use Magento\Config\Model\Config\SourceFactory;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select as BaseSelect;

class Select extends BaseSelect
{
    /**
     * @var SourceFactory
     */
    private $sourceFactory;

    /**
     * RowSelect constructor.
     *
     * @param Context $context
     * @param SourceFactory $sourceFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        SourceFactory $sourceFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->sourceFactory = $sourceFactory;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }

        $this->setData('class', $this->getColumn()['class']);

        return parent::_toHtml();
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * @return string
     */
    public function setInputName($value)
    {
        return $this->setName($value . '[]');
    }

    /**
     * @return array
     */
    public function getSourceOptions(): array
    {
        return $this->sourceFactory->create($this->getSourceModel())->toOptionArray();
    }
}
