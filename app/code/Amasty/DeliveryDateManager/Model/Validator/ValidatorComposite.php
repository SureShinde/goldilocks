<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model\Validator;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\ValidatorInterface;

class ValidatorComposite extends AbstractValidator
{
    /**
     * @var ValidatorInterface[]
     */
    private $validators;

    public function __construct(
        array $validators = []
    ) {
        $this->validators = $validators;
    }

    /**
     * @param AbstractModel $object
     * @return bool
     * @throws \Zend_Validate_Exception
     */
    public function isValid($object): bool
    {
        $result = true;
        $this->_clearMessages();

        foreach ($this->validators as $validator) {
            if (!$validator->isValid($object)) {
                $result = false;
                $this->_addMessages($validator->getMessages());
            }
        }

        return $result;
    }
}
