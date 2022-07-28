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
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\ViewModel\Adminhtml\Form;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Plumrocket\PrivateSale\Api\EventRepositoryInterface;

class Element extends AbstractElement implements ArgumentInterface
{
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Element constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param FormFactory $formFactory
     * @param EventRepositoryInterface $eventRepository
     * @param RequestInterface $request
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        FormFactory $formFactory,
        EventRepositoryInterface $eventRepository,
        RequestInterface $request,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->formFactory = $formFactory;
        $this->eventRepository = $eventRepository;
        $this->request = $request;
    }

    /**
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getForm()
    {
        if (null === $this->_form) {
            $this->_form = $this->formFactory->create();
        }

        return $this->_form;
    }

    /**
     * @return array
     */
    public function getValue()
    {
        try {
            $event = $this->eventRepository->getById($this->request->getParam('id'));
            $fieldValue = $event->getData($this->getName());

            if ($fieldValue) {
                return $fieldValue;
            }
        } catch (NoSuchEntityException $e) {}

        return $this->getDefaultValue();
    }
}
