<?php

declare(strict_types=1);

namespace Amasty\PreOrderAnalytic\Plugin\Sales\Controller\Adminhtml\Order\Index;

use Amasty\Preorder\Api\Data\OrderInformationInterface;
use Amasty\PreOrderAnalytic\Model\Grid\BookmarkManagement;
use Magento\Sales\Controller\Adminhtml\Order\Index as OrderGridController;

class AddPreorderFilter
{
    const ORDER_GRID_BOOKMARK = 'sales_order_grid';

    /**
     * @var BookmarkManagement
     */
    private $bookmarkManagement;

    public function __construct(BookmarkManagement $bookmarkManagement)
    {
        $this->bookmarkManagement = $bookmarkManagement;
    }

    public function beforeExecute(OrderGridController $subject): void
    {
        if ($value = (int) $subject->getRequest()->getParam(OrderInformationInterface::PREORDER_FLAG)) {
            $this->bookmarkManagement->applyFilter(self::ORDER_GRID_BOOKMARK, [
                OrderInformationInterface::PREORDER_FLAG => $value
            ]);
            $this->bookmarkManagement->clear();
        }
    }
}
