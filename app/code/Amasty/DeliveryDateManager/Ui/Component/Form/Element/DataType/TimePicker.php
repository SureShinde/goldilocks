<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Element\DataType;

use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class TimePicker extends \Magento\Ui\Component\Form\Element\DataType\Date
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    public function __construct(
        ContextInterface $context,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        array $components = [],
        array $data = []
    ) {
        $this->localeDate = $localeDate;
        parent::__construct($context, $localeDate, $localeResolver, $components, $data);
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');

        $config['options']['dateFormat'] = '';
        $config['options']['timeFormat'] = $this->localeDate->getTimeFormat();

        $this->setData('config', $config);
    }
}
