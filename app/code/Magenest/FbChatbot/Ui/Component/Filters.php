<?php

namespace Magenest\FbChatbot\Ui\Component;

class Filters extends \Magento\Ui\Component\Filters
{
    protected $filterMap = [
        'text' => 'filterInput',
        'textRange' => 'filterRange',
        'numberRange' => 'filterRange',
        'select' => 'filterSelect',
        'dateRange' => 'filterDate',
    ];
}
