<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component\Grid\Columns;

class Date extends \Magento\Ui\Component\Listing\Columns\Date
{
    public function prepare()
    {
        $config = $this->getData('config');
        $config['dateFormat'] = $this->timezone->getDateFormat(\IntlDateFormatter::MEDIUM);
        $this->setData('config', $config);

        parent::prepare();
    }
}
