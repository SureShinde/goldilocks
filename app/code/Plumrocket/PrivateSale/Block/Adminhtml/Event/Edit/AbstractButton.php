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
 * @package     Plumrocket_PrivateSale
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Block\Adminhtml\Event\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Plumrocket\PrivateSale\Model\EventRepository;

abstract class AbstractButton implements ButtonProviderInterface
{
    /**
     * @var EventRepository
     */
    private $eventRepository;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param Context $context
     * @param EventRepository $eventRepository
     */
    public function __construct(
        Context $context,
        EventRepository $eventRepository
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->request = $context->getRequest();
        $this->eventRepository = $eventRepository;
    }

    /**
     * Return Event
     *
     * @return \Plumrocket\PrivateSale\Api\Data\EventInterface
     */
    public function getEvent()
    {
        try {
            return $this->eventRepository->getById($this->request->getParam('id'));
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    protected function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * @param string $param
     * @param null $default
     * @return string|null
     */
    protected function getRequestParam(string $param, $default = null)
    {
        return $this->request->getParam($param, $default);
    }
}
