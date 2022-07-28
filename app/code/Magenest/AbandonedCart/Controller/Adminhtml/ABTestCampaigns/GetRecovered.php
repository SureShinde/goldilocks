<?php


namespace Magenest\AbandonedCart\Controller\Adminhtml\ABTestCampaigns;

use Magenest\AbandonedCart\Model\AbandonedCart;
use Magenest\AbandonedCart\Model\Config\Source\Mail as EmailStatus;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magenest\AbandonedCart\Model\ResourceModel\AbandonedCart\CollectionFactory as ABCollection;
use Magento\Framework\Controller\ResultFactory;
use Zend_Db_Select;

class GetRecovered extends Action
{
    /** @var ABCollection */
    protected $_abCollection;

    public function __construct(
        Action\Context $context,
        ABCollection $abCollection
    ) {
        $this->_abCollection = $abCollection;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData([
            'message' => 'done',
            'first_rule' => $this->getDataRecovered($params['from_first'], $params['to_first'], $params['rule_first']),
            'second_rule' => $this->getDataRecovered($params['from_second'], $params['to_second'], $params['rule_second']),
        ]);
    }

    /**
     * @param $from
     * @param $to
     * @return array
     */
    public function getDataRecovered($from, $to, $ruleId)
    {
        $collection = $this->_abCollection->create();
        $collection->clear()->getSelect()->reset(\Zend_Db_Select::WHERE);
        $collection->addFieldToFilter('main_table.created_at', ['gteq' => $from])
            ->addFieldToFilter('main_table.created_at', ['lteq' => $to])
            ->addFieldToFilter('main_table.status', AbandonedCart::STATUS_RECOVERED)
            ->addFieldToFilter('main_table.rule_id', $ruleId);
        $collection->getSelect()
            ->joinLeft(
                ['q' => $collection->getTable('quote')],
                'q.entity_id = main_table.quote_id',
                ['q.items_qty']
            );
        $collection->load();
        return [
            'recovered_cart' => $collection->count(),
            'recovered_product' => array_sum($collection->getColumnValues('items_qty'))
        ];
    }
}
