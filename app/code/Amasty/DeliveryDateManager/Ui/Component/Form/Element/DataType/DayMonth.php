<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Form\Element\DataType;

use Amasty\DeliveryDateManager\Model\DeliveryDate\DateFormatProvider;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class DayMonth extends \Magento\Ui\Component\Form\Element\DataType\Date
{
    /**
     * @var DateFormatProvider
     */
    private $dateFormatProvider;

    public function __construct(
        ContextInterface $context,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        DateFormatProvider $dateFormatProvider,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $localeDate, $localeResolver, $components, $data);
        $this->dateFormatProvider = $dateFormatProvider;
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

        $config['options']['dateFormat'] = $this->dateFormatProvider->getDateFormatWithoutYear();

        $this->setData('config', $config);
    }
}
