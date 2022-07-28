<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Adminhtml\Sales\Order\Renderer;

use Amasty\DeliveryDateManager\Model\ConfigProvider;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Date Component
 */
class Date extends \Magento\Framework\Data\Form\Element\Date
{
    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        TimezoneInterface $localeDate,
        ConfigProvider $configProvider,
        Json $json,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $localeDate, $data);
        $this->localeDate = $localeDate;
        $this->configProvider = $configProvider;
        $this->json = $json;
    }

    /**
     * @return string
     */
    public function getElementHtml(): string
    {
        $this->addClass('admin__control-text input-text');
        $jsonData = $this->json->serialize(
            [
                'calendar' => [
                    'dateFormat' => $this->localeDate->getDateFormat(),
                    'showsTime' => false,
                    'buttonText' => 'Select Date',
                    'firstDay' => $this->configProvider->getFirstDayOfWeek(),
                    'minDate' => $this->localeDate->formatDate()
                ]
            ]
        );

        $dataInit = 'data-mage-init="' . $this->_escape($jsonData) . '"';

        $html = sprintf(
            '<input name="%s" id="%s" value="%s" %s %s />',
            $this->getName(),
            $this->getHtmlId(),
            $this->_escape($this->getValue()),
            $this->serialize($this->getHtmlAttributes()),
            $dataInit
        );
        $html .= $this->getAfterElementHtml();

        return $html;
    }
}
