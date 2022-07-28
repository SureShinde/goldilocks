<?php

namespace Amasty\Rolepermissions\Plugin\Catalog\Ui\Component\Listing;

class Columns
{
    /** @var \Amasty\Rolepermissions\Helper\Data $helper */
    protected $helper;

    /** @var \Magento\Framework\Registry $registry */
    protected $registry;

    public function __construct(
        \Amasty\Rolepermissions\Helper\Data $helper,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->registry = $registry;
    }

    public function afterGetChildComponents($subject, $result)
    {
        /** @var \Amasty\Rolepermissions\Model\Rule $model */
        $model = $this->helper->currentRule();
        if ($model->getAttributes()) {
            $notRemoveKeys = [
                'actions',
                'ids',
                'entity_id',
                'type_id',
                'websites',
                'qty',
                'attribute_set_id',
            ];

            $allowedAttributeCodes = $this->helper->getAllowedAttributeCodes();

            if (is_array($allowedAttributeCodes)) {
                foreach ($result as $key => $value) {
                    if (!in_array($key, $allowedAttributeCodes) && !in_array($key, $notRemoveKeys)) {
                        unset($result[$key]);
                    }
                }
            }
        }

        return $result;
    }
}
