<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Request\Validator;

use Magento\Framework\App\RequestInterface;

interface DDEditValidatorInterface
{
    /**
     * @param RequestInterface $request
     * @return ValidatorResult
     */
    public function validateRequest(RequestInterface $request): ValidatorResult;
}
