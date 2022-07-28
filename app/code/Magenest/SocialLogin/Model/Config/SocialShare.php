<?php
namespace Magenest\SocialLogin\Model\Config;

/**
 * Class SocialShare
 * @package Magenest\SocialLogin\Model\Config
 */
class SocialShare implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'twitter',
                'label' => __('Twitter'),
            ],
            [
                'value' => 'facebook',
                'label' => __('Facebook'),
            ],
            [
                'value' => 'linkedin',
                'label' => __('LinkedIn'),
            ],
            [
                'value' => 'pinterest',
                'label' => __('Pinterest'),
            ],
            [
                'value' => 'reddit',
                'label' => __('Reddit'),
            ],
            [
                'value' => 'line',
                'label' => __('Line'),
            ]
        ];
    }
}
