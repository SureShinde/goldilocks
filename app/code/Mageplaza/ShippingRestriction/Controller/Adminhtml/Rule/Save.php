<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ShippingRestriction
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ShippingRestriction\Controller\Adminhtml\Rule;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Mageplaza\ShippingRestriction\Controller\Adminhtml\Rule;
use Mageplaza\ShippingRestriction\Helper\Data;
use Mageplaza\ShippingRestriction\Model\ResourceModel\Rule as RuleResource;
use Mageplaza\ShippingRestriction\Model\RuleFactory;
use RuntimeException;

/**
 * Class Save
 * @package Mageplaza\ShippingRestriction\Controller\Adminhtml\Rule
 */
class Save extends Rule
{
    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var Date
     */
    protected $dateFilter;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param RuleFactory $ruleFactory
     * @param RuleResource $ruleResource
     * @param DateTime $date
     * @param Data $helperData
     * @param Date $dateFilter
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RuleFactory $ruleFactory,
        RuleResource $ruleResource,
        DateTime $date,
        Data $helperData,
        Date $dateFilter
    ) {
        $this->date = $date;
        $this->_helperData = $helperData;
        $this->dateFilter = $dateFilter;

        parent::__construct(
            $ruleFactory,
            $registry,
            $context,
            $ruleResource
        );
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPostValue()) {
            /** @var \Mageplaza\ShippingRestriction\Model\Rule $rule */
            $rule = $this->initRule();
            $this->prepareData($rule, $data['rule']);
            $id = isset($data['rule']['rule_id']) ? $data['rule']['rule_id'] : '';

            /** get rule conditions */
            $rule->loadPost($data['rule']);
            $this->_eventManager->dispatch(
                'mageplaza_shippingrestriction_rule_prepare_save',
                ['post' => $rule, 'request' => $this->getRequest()]
            );

            try {
                $this->ruleResource->save($rule);
                $this->messageManager->addSuccessMessage(__('The rule has been saved.'));
                $this->_getSession()->setData('mageplaza_shippingrestriction_rule_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'mpshippingrestriction/*/edit',
                        ['id' => $rule->getId(), '_current' => true]
                    );
                } else {
                    $resultRedirect->setPath('mpshippingrestriction/*/');
                }

                return $resultRedirect;
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Rule.'));
                if (!empty($id)) {
                    $resultRedirect->setPath('mpshippingrestriction/*/edit', ['id' => $id, '_current' => true]);
                } else {
                    $resultRedirect->setPath('mpshippingrestriction/*/');
                }

                return $resultRedirect;
            }

            $this->_getSession()->setData('mageplaza_shippingrestriction_rule_data', $data);

            $resultRedirect->setPath('mpshippingrestriction/*/edit', ['id' => $rule->getId(), '_current' => true]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('mpshippingrestriction/*/');

        return $resultRedirect;
    }

    /**
     * @param \Mageplaza\ShippingRestriction\Model\Rule $rule
     * @param array $data
     *
     * @return $this
     */
    protected function prepareData($rule, $data = [])
    {
        if ($rule->getCreatedAt() === null) {
            $data['created_at'] = $this->date->date();
        }
        $data['started_at'] = $data['started_at_name'] === '' ?
            $this->date->date('Y-m-d') : $data['started_at_name'];

        try {
            $this->dateFilter->filter($data['started_at']);
        } catch (Exception $exception) {
            $data['started_at'] = null;
        }

        try {
            $this->dateFilter->filter($data['finished_at']);
        } catch (Exception $exception) {
            $data['finished_at'] = null;
        }

        $data['updated_at'] = $this->date->date();

        if (isset($data['schedule_name'])) {
            $data['schedule'] = Data::jsonEncode($data['schedule_name']);
        }
        if (!isset($data['payment_methods'])) {
            $data['payment_methods'] = [];
        }
        $rule->addData($data);

        return $this;
    }
}
