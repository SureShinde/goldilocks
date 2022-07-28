<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket Private Sales and Flash Sales v4.x.x
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Plugin\Price;

use Magento\Framework\Pricing\SaleableInterface;
use Plumrocket\PrivateSale\Model\Frontend\ProductEventPermissions;
use Plumrocket\PrivateSale\Model\Integration\PopupLogin as PopupLoginModel;

/**
 * Disable render price depends on events permissions
 * Also set message instead of price or add to cart
 *
 * @since v5.0.0
 */
class Render
{
    const ALLOW_PRICE_CODE = 'final_price';

    /**
     * @var ProductEventPermissions
     */
    private $productEventPermissions;

    /**
     * @var \Plumrocket\PrivateSale\Model\Integration\PopupLogin
     */
    private $popupLoginModel;

    /**
     * Render constructor.
     *
     * @param ProductEventPermissions                              $productEventPermissions
     * @param \Plumrocket\PrivateSale\Model\Integration\PopupLogin $popupLoginModel
     */
    public function __construct(
        ProductEventPermissions $productEventPermissions,
        PopupLoginModel $popupLoginModel
    ) {
        $this->productEventPermissions = $productEventPermissions;
        $this->popupLoginModel = $popupLoginModel;
    }

    /**
     * @param \Magento\Framework\Pricing\Render $subject
     * @param \Closure $proceed
     * @param $priceCode
     * @param SaleableInterface $saleableItem
     * @param array $arguments
     * @return string
     */
    public function aroundRender(
        \Magento\Framework\Pricing\Render $subject,
        \Closure $proceed,
        $priceCode,
        SaleableInterface $saleableItem,
        array $arguments = []
    ) {
        $productId = (int) $saleableItem->getId();

        if ($priceCode === self::ALLOW_PRICE_CODE) {
            if (! $this->productEventPermissions->canShowPrice($productId)) {
                return $this->wrapMessage(
                    $productId,
                    $this->productEventPermissions->getPriceMessageData($productId)
                );
            }

            $addToCartMessageHtml = $this->wrapMessage(
                $productId,
                $this->productEventPermissions->getAddToCartMessageData($productId)
            );

            return $proceed($priceCode, $saleableItem, $arguments) . $addToCartMessageHtml;
        }

        return $this->productEventPermissions->canShowPrice($productId)
            ? $proceed($priceCode, $saleableItem, $arguments)
            : '';
    }

    /**
     * @param int   $productId
     * @param array $messageData
     * @return string
     */
    private function wrapMessage(int $productId, array $messageData): string
    {
        if ($messageData['text']) {
            $html = "<p class=\"prprivatesale_product_msg\" id=\"prprivatesale_hide_price_{$productId}\">";

            if ($messageData['landingPage']) {
                $popupLoginForm = $this->popupLoginModel->getFormType($messageData['landingPage']);

                if ($popupLoginForm) {
                    $html .= "<a class=\"show_popup_login\" href=\"#\" data-form=\"{$popupLoginForm}\">";
                } else {
                    $html .= "<a href=\"{$messageData['url']}\">";
                }
            }

            $html .= $messageData['text'];

            if ($messageData['landingPage']) {
                $html .= '</a>';
            }

            $html .= '</p>';

            return $html;
        }

        return '';
    }
}
