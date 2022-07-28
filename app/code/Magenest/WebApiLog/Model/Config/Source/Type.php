<?php
/**
 * Created by PhpStorm.
 */
namespace Magenest\WebApiLog\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Type
 * @package Magenest\WebApiLog\Model\Config\Source
 */
class Type extends AbstractSource
{

    const API_REQUEST  = 1;
    const API_RESPONSE = 2;

    /**
     * @var
     */
    protected $_pdfinvoiceCollection;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [self::API_REQUEST => __('Request'), self::API_RESPONSE => __('Response')];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];
        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

}
