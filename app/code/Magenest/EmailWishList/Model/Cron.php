<?php

namespace Magenest\EmailWishList\Model;
use Magenest\EmailWishList\Helper\Data\WishlistProducts;

class Cron
{
    /**
     * @var WishlistProducts
     */
    protected $helperData;

    /**
     * @param WishlistProducts $helperData
     */
    public function __construct(
        WishlistProducts $helperData
    )
    {
        $this->helperData = $helperData;
    }

    /**
     * @return void
     */
    public function sendScheduledEmailWishlist() {

        $check = $this->helperData->checkUpdateWishList();

    }
}
