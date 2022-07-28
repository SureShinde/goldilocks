<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magenest\Ipay88\Plugin\Framework\App\Request;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\CsrfValidator;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CsrfByPass
 * @package Magenest\AlePay\Plugin\Framework\App\Request
 */
class CsrfByPass
{
    /** @const */
    const BY_PASS_URI = ['/ipay88/payment/response'];

    /**
     * Around validate
     *
     * @param CsrfValidator $validator
     * @param callable $proceed
     * @param RequestInterface $request
     * @param ActionInterface $action
     */
    public function aroundValidate(
        CsrfValidator $validator,
        callable $proceed,
        RequestInterface $request,
        ActionInterface $action
    ) {
        try {
            /** @var State $appState */
            $appState = ObjectManager::getInstance()->get(State::class);
            $areaCode = $appState->getAreaCode();
        } catch (LocalizedException $exception) {
            $areaCode = null;
        }

        if ($request instanceof HttpRequest
            && in_array($areaCode, [Area::AREA_FRONTEND, Area::AREA_ADMINHTML], true)
        ) {
            if (!in_array($request->getPathInfo(), self::BY_PASS_URI)) {
                $proceed($request, $action);
            }
        }
    }
}
