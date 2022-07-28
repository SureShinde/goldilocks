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

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\ObjectManagerInterface;

class AbstractTable extends AbstractFieldArray
{
    /**
     * @var int
     */
    protected $rowIndex = 0;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * AbstractTable constructor.
     * @param Context $context
     * @param DataObjectFactory $dataObjectFactory
     * @param ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        DataObjectFactory $dataObjectFactory,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->objectManager = $objectManager;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getDefaultTableValues()
    {
        return $this->dataObjectFactory->create()->setData($this->getDefaultValues());
    }

    /**
     * @param $value
     * @return $this
     */
    public function setElementData($name, $value)
    {
        if (! is_object($this->getElement())) {
            $element = $this->objectManager->create($this->getElement())
                ->setData($name, $value);

            $this->setElement($element);
        } else {
            $this->getElement()->setData($name, $value);
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getDefaultValues()
    {
        return [
            [
                'label' => __('Browsing Event'),
                'status' => 'off',
            ],
            [
                'label' => __('Show Product Prices'),
                'status' => 'off',
            ],
            [
                'label' => __('Show Add to Cart Button'),
                'status' => 'off',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        if (null === $row->getStatus()) {
            $row->setStatus('off');
        }

        parent::_prepareArrayRow($row);
    }

    /**
     * @return array
     */
    protected function getSingleColumns()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        $this->_prepareToRender();
        return parent::_toHtml();
    }
}
