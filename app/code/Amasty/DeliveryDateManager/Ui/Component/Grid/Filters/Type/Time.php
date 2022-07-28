<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Grid\Filters\Type;

use Amasty\DeliveryDateManager\Model\TimeInterval\TimeToMinsConverter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Filters\Type\Date;

class Time extends Date
{
    /**
     * @var TimeToMinsConverter
     */
    private $timeToMinsConverter;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterBuilder $filterBuilder,
        FilterModifier $filterModifier,
        TimeToMinsConverter $timeToMinsConverter,
        TimezoneInterface $localeDate,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $filterBuilder, $filterModifier, $components, $data);
        $this->timeToMinsConverter = $timeToMinsConverter;
        $this->localeDate = $localeDate;
    }

    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');

        $timeFormat = $this->localeDate->getTimeFormat();

        $config['templates']['datetime']['options']['dateFormat'] = '';
        $config['templates']['datetime']['options']['timeFormat'] = $timeFormat;

        $this->setData('config', $config);
    }

    /**
     * Apply filter
     *
     * @return void
     */
    protected function applyFilter()
    {
        if (isset($this->filterData[$this->getName()])) {
            $value = $this->filterData[$this->getName()];

            if (empty($value)) {
                return;
            }

            if (is_array($value)) {
                if (isset($value['from'])) {
                    $timeFrom = (string)$this->timeToMinsConverter->execute((string)$value['from']);
                    $this->applyFilterByType('gteq', $timeFrom);
                }

                if (isset($value['to'])) {
                    $timeTo = (string)$this->timeToMinsConverter->execute((string)$value['to']);
                    $this->applyFilterByType('lteq', $timeTo);
                }
            } else {
                $this->applyFilterByType('eq', (string)$value);
            }
        }
    }

    /**
     * Apply filter by its type
     *
     * @param string $type
     * @param string $value
     * @return void
     */
    protected function applyFilterByType($type, $value)
    {
        if (!empty($value)) {
            $filter = $this->filterBuilder->setConditionType($type)
                ->setField($this->getName())
                ->setValue($value)
                ->create();

            $this->getContext()->getDataProvider()->addFilter($filter);
        }
    }
}
