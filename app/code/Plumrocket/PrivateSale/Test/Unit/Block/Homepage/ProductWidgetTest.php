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
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\PrivateSale\Test\Unit\Block\Homepage;

use Plumrocket\PrivateSale\Block\Homepage\ProductWidget;

class ProductWidgetTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var null | ProductWidget
     */
    private $block = null;

    protected function setUp(): void
    {
        $this->block = $this->getMockBuilder(ProductWidget::class)
            ->setMethodsExcept(['replaceWidgetTitle'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @dataProvider getDataExampleForTest()
     * @param $arguments
     * @param $expect
     * @param bool $exception
     */
    public function testReplaceWidgetTitle($html, $title, $expect)
    {
        $this->assertEquals($expect, $this->block->replaceWidgetTitle($html, $title));
    }

    public function getDataExampleForTest()
    {
        return [
            [
                'html' => '<div class="block widget block-new-products grid">
                                <div class="block-title">
                                    <strong role="heading" aria-level="2">New Products</strong>
                                </div>',
                'title' => 'Event Widget',
                'expect' => '<div class="block widget block-new-products grid">
                                <div class="block-title">
                                    <strong role="heading" aria-level="2">Event Widget</strong>
                                </div>',
            ],
            [
                'html' => '<div class="block widget block-new-products grid">
                                <div class="block-title">
                                    <strong role="heading" aria-level="2">New Products</strong>
                                </div>',
                'title' => '',
                'expect' => '<div class="block widget block-new-products grid">
                                <div class="block-title">
                                    <strong role="heading" aria-level="2"></strong>
                                </div>',
            ],
            [
                'html' => '<div class="block widget block-new-products list">
                                <div class="block-title">
                                    <strong role="heading" aria-level="2">New Products</strong>
                                </div>',
                'title' => 'Event Widget',
                'expect' => '<div class="block widget block-new-products list">
                                <div class="block-title">
                                    <strong role="heading" aria-level="2">Event Widget</strong>
                                </div>',
            ],
            [
                'html' => '<div class="block widget block-new-products list">
                                <div class="block-title">
                                    <strong role="heading" aria-level="2">New Products</strong>
                                </div>',
                'title' => '',
                'expect' => '<div class="block widget block-new-products list">
                                <div class="block-title">
                                    <strong role="heading" aria-level="2"></strong>
                                </div>',
            ]
        ];
    }
}
