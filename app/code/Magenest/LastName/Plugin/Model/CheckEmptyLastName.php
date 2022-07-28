<?php

namespace Magenest\LastName\Plugin\Model;

use Magento\Customer\Model\ResourceModel\Customer\Collection;

/**
 * Class CheckEmptyLastName
 * @package Magenest\LastName\Plugin\Model
 */
class CheckEmptyLastName
{
    /**
     * @var \Magenest\LastName\Helper\Data
     */
    protected $_helperLastName;
    /**
     * @var \Magento\Framework\DataObject\Copy\Config
     */
    protected $_fieldsetConfig;

    /**
     * CheckEmptyLastName constructor.
     * @param \Magenest\LastName\Helper\Data $helperLastName
     * @param \Magento\Framework\DataObject\Copy\Config $fieldsetConfig
     */
    public function __construct(
        \Magenest\LastName\Helper\Data $helperLastName,
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig
    ) {
        $this->_fieldsetConfig = $fieldsetConfig;
        $this->_helperLastName = $helperLastName;
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Customer\Collection $subject
     * @param callable $proceed
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundAddNameToSelect(\Magento\Sales\Model\ResourceModel\Order\Customer\Collection $subject, callable $proceed)
    {
        /* if only First Name is required */
        if (!$this->_helperLastName->isLastnameRequired()) {
            return $this->addNameToSelectExtend($subject);
        } else {
            return $proceed();
        }
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addNameToSelectExtend($subject)
    {
        $fields = [];
        $customerAccount = $this->_fieldsetConfig->getFieldset('customer_account');
        foreach ($customerAccount as $code => $field) {
            if (isset($field['name'])) {
                $fields[$code] = $code;
            }
        }

        $connection = $subject->getConnection();
        $concatenate = [];
        if (isset($fields['prefix'])) {
            $concatenate[] = $connection->getCheckSql(
                '{{prefix}} IS NOT NULL AND {{prefix}} != \'\'',
                $connection->getConcatSql(['LTRIM(RTRIM({{prefix}}))', '\' \'']),
                '\'\''
            );
        }
        $concatenate[] = 'LTRIM(RTRIM({{firstname}}))';
        $concatenate[] = '\' \'';
        if (isset($fields['middlename'])) {
            $concatenate[] = $connection->getCheckSql(
                '{{middlename}} IS NOT NULL AND {{middlename}} != \'\'',
                $connection->getConcatSql(['LTRIM(RTRIM({{middlename}}))', '\' \'']),
                '\'\''
            );
        }
        if (isset($fields['lastname'])) {
            $concatenate[] = $connection->getCheckSql(
                '{{lastname}} IS NOT NULL AND {{lastname}} != \'\'',
                $connection->getConcatSql(['\' \'', 'LTRIM(RTRIM({{lastname}}))']),
                '\'\''
            );
        }
        if (isset($fields['suffix'])) {
            $concatenate[] = $connection->getCheckSql(
                '{{suffix}} IS NOT NULL AND {{suffix}} != \'\'',
                $connection->getConcatSql(['\' \'', 'LTRIM(RTRIM({{suffix}}))']),
                '\'\''
            );
        }

        $nameExpr = $connection->getConcatSql($concatenate);

        $subject->addExpressionAttributeToSelect('name', $nameExpr, $fields);

        return $subject;
    }
}
