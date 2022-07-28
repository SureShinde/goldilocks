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
 * @package     Mageplaza_PaymentRestriction
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\PaymentRestriction\Controller\Adminhtml\Rule;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Mageplaza\PaymentRestriction\Controller\Adminhtml\Rule;
use Mageplaza\PaymentRestriction\Helper\Data;
use Mageplaza\PaymentRestriction\Model\RuleFactory;
use RuntimeException;

/**
 * Class Save
 * @package Mageplaza\PaymentRestriction\Controller\Adminhtml\Rule
 */
class Save extends Rule
{
    /**
     * JS helper
     *
     * @var Js
     */
    public $jsHelper;

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
     * @param Js $jsHelper
     * @param DateTime $date
     * @param Date $dateFilter
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RuleFactory $ruleFactory,
        Js $jsHelper,
        DateTime $date,
        Date $dateFilter,
        Data $helperData
    ) {
        $this->jsHelper = $jsHelper;
        $this->date = $date;
        $this->_helperData = $helperData;
        $this->dateFilter = $dateFilter;

        parent::__construct($ruleFactory, $registry, $context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPostValue()) {
            /** @var \Mageplaza\PaymentRestriction\Model\Rule $rule */
            $rule = $this->initRule();
            $this->prepareData($rule, $data['rule']);
            $id = isset($data['rule']['rule_id']) ? $data['rule']['rule_id'] : '';

            /** get rule conditions */
            $rule->loadPost($data['rule']);
            $this->_eventManager->dispatch(
                'mageplaza_paymentrestriction_rule_prepare_save',
                ['post' => $rule, 'request' => $this->getRequest()]
            );

            try {
                $rule->save();

                $this->messageManager->addSuccess(__('The rule has been saved.'));
                $this->_getSession()->setData('mageplaza_paymentrestriction_rule_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'mppaymentrestriction/*/edit',
                        ['id' => $rule->getId(), '_current' => true]
                    );
                } else {
                    $resultRedirect->setPath('mppaymentrestriction/*/');
                }

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Rule.'));
                if (!empty($id)) {
                    $resultRedirect->setPath('mppaymentrestriction/*/edit', ['id' => $id, '_current' => true]);
                } else {
                    $resultRedirect->setPath('mppaymentrestriction/*/');
                }

                return $resultRedirect;
            }

            $this->_getSession()->setData('mageplaza_paymentrestriction_rule_data', $data);

            $resultRedirect->setPath('mppaymentrestriction/*/edit', ['id' => $rule->getId(), '_current' => true]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('mppaymentrestriction/*/');

        return $resultRedirect;
    }

    /**
     * @param $rule
     * @param array $data
     * @return $this
     * @throws Exception
     */
    protected function prepareData($rule, $data = [])
    {
        if ($rule->getCreatedAt() == null) {
            $data['created_at'] = $this->date->date();
        }
        $data['started_at'] = ($data['started_at_name'] == '') ?
            $this->date->date('Y-m-d') : $data['started_at_name'];
        try {
            $data['started_at'] = $this->dateFilter->filter($data['started_at']);
        } catch (Exception $exception) {
            $data['started_at'] = null;
        }

        try {
            $data['finished_at'] = $this->dateFilter->filter($data['finished_at']);
        } catch (Exception $exception) {
            $data['finished_at'] = null;
        }

        $data['updated_at'] = $this->date->date();

        if (isset($data['schedule_name'])) {
            $data['schedule'] = $this->_helperData->jsonEncode($data['schedule_name']);
        }
        if (!isset($data['payment_methods'])) {
            $data['payment_methods'] = [];
        }
        $rule->addData($data);

        return $this;
    }
}
