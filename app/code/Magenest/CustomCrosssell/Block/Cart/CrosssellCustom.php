<?php

namespace Magenest\CustomCrosssell\Block\Cart;

use Magento\Checkout\Block\Cart\Crosssell;

class CrosssellCustom extends Crosssell
{
    protected $_maxItemCount = 5;
}
