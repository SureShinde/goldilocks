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

use Plumrocket\PrivateSale\Model\Config\Source\CustomerGroup;

class PrivateSalePermissions extends AbstractTable
{
    /**
     * @inheritDoc
     */
    protected $_template = 'Plumrocket_PrivateSale::form/field/rows.phtml';

    /**
     * @var Select
     */
    private $selectRenderer;

    /**
     * @inheritDoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn('group', [
            'label' => __('Customer Group'),
            'class' => 'permission',
            'renderer' => $this->getSelectRenderer()
                ->setExtraParams(
                    'multiple="multiple" data-form-part="prprivatesale_event_form" data-validate="{permission :true}"'
                )
        ]);

        $this->addColumn('label', [
            'label' => __(''),
            'renderer' => $this->getLayout()->createBlock(Label::class),
        ]);

        $this->addColumn('status', [
            'label' => __(''),
            'renderer' => $this->getLayout()->createBlock(Toggle::class),
        ]);

        $this->addColumn('action', [
            'label' => __('Action'),
            'renderer' => $this->getLayout()->createBlock(Trash::class),
        ]);

        foreach ($this->getSingleColumns() as $column) {
            $this->_columns[$column]['rowspan'] = 3;
        }

        $this->_isPreparedToRender = true;
        $this->_addAfter = true;
        parent::_prepareToRender();
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        $this->rowIndex = 0;
        return parent::_toHtml();
    }

    /**
     * @inheritDoc
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $this->rowIndex++;
        $options = [];

        if (1 !== $this->rowIndex) {
            foreach ($this->getSingleColumns() as $column) {
                $row->unsetData($column);
            }
        } else {
            if (null === $row->getAction()) {
                $row->setAction(true);
            }

            if (null === $row->getGroup()) {
                $row->setGroup([]);
            } elseif (is_array($row->getGroup())) {
                foreach ($row->getGroup() as $optionValue) {
                    $options['option_' . $this->getSelectRenderer()->calcOptionHash($optionValue)]
                        = 'selected="selected"';
                }
            } else {
                foreach ($this->getSelectRenderer()->getSourceOptions() as $optionValue) {
                    $options['option_' . $this->getSelectRenderer()->calcOptionHash($optionValue['value'])]
                        = 'selected="selected"';
                }
            }

            $row->setData('option_extra_attrs', $options);
        }

        if ($this->rowIndex === 3) {
            $this->rowIndex = 0;
        }

        parent::_prepareArrayRow($row);
    }

    /**
     * @return array
     */
    protected function getSingleColumns()
    {
        return ['group', 'action'];
    }

    /**
     * @return array
     */
    protected function getDefaultValues()
    {
        return [
            [
                'group' => null,
                'label' => __('Browsing Event'),
                'status' => 'on',
                'action' => null,
                'option_extra_attrs' => [],
            ],
            [
                'label' => __('Show Product Prices'),
                'status' => 'on',
            ],
            [
                'label' => __('Show Add to Cart Button'),
                'status' => 'on',
            ],
        ];
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getSelectRenderer()
    {
        if (!$this->selectRenderer) {
            $this->selectRenderer = $this->getLayout()->createBlock(
                Select::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            )->setSourceModel(CustomerGroup::class);
        }
        return $this->selectRenderer;
    }
}
