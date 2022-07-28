<?php

declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Ui\Component;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

abstract class AbstractModifier implements ModifierInterface
{
    public const CHANNEL_REQUEST_ID = 'id';

    public const FORM_NAME = 'amdelivery_channel_form';

    public const CONFIG_DATA = 'config_data';

    public const DATA_SOURCE_DEFAULT = 'channel_data';

    public const ORDER_LIMIT_DATA = 'order_limit';

    public const CHANNEL_SCHEDULES_DATA = 'data_schedules';

    public const CHANNEL_INTERVALS = 'data_intervals';

    public const FORM_GENERAL_SCHEDULE_RELATION = 'schedules';

    public const FORM_GENERAL_EXCEPTIONS_RELATION = 'exceptions';
}
