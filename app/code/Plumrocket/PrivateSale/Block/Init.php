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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\PrivateSale\Block;

use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;
use Plumrocket\PrivateSale\Helper\Preview;
use Plumrocket\PrivateSale\Helper\Timer;

class Init extends \Magento\Framework\View\Element\Template
{
    /**
     * Registry
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Preview
     */
    protected $previewHelper;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Timer
     */
    private $timerHelper;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * Init constructor.
     *
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Plumrocket\PrivateSale\Helper\Preview           $previewHelper
     * @param \Plumrocket\PrivateSale\Helper\Timer             $timerHelper
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param array                                            $data
     */
    public function __construct(
        Registry $registry,
        Context $context,
        Preview $previewHelper,
        Timer $timerHelper,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
        $this->previewHelper = $previewHelper;
        $this->timerHelper = $timerHelper;
        $this->serializer = $serializer;
    }

    /**
     * @return bool|string
     */
    public function getSerializedConfigs()
    {
        return $this->serializer->serialize([
            '#privatesale-event-init' => [
                'privatesaleEvent' => [
                    'timeUrl'                  => $this->getUrl('prprivatesale/ajax/timeAction'),
                    'privateSaleCacheCleanUrl' => $this->getUrl('prprivatesale/ajax/cleancacheAction'),
                    'previewDate'              => $this->previewHelper->getPreviewDate(),
                    'previewMode'              => $this->previewHelper->getPreviewMode(),
                    'countdownLabelsFew'       => __('years,months,weeks,days,hours,minutes,seconds'),
                    'countdownLabelsOne'       => __('year,month,week,day,hour,minute,second'),
                    'countdownLayouts'         => $this->timerHelper->getCountdownLayouts()
                ],
            ],
        ]);
    }
}
