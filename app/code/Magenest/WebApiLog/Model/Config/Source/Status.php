<?php
/**
 * Created by PhpStorm.
 */
namespace Magenest\WebApiLog\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Status
 * @package Magenest\WebApiLog\Model\Config\Source
 */
class Status extends AbstractSource
{
    const SUCCESS = '1';
    const ERROR   = '0';
    /**
     * @var
     */
    protected $_pdfinvoiceCollection;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [self::SUCCESS => __('Success'), self::ERROR => __('Error')];
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
