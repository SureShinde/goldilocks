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
 * @copyright   Copyright (c) 2016 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

namespace Plumrocket\PrivateSale\Block\Adminhtml\Emailtemplate;

class Generator extends \Magento\Backend\Block\Template
{
    const DEFAULT_DATE_FORMAT = 'm/d/Y';

    /**
     * List html
     * @var string
     */
    protected $_listHtml;

    /**
     * Start date
     * @var string
     */
    protected $_startDate;

    /**
     * End date
     * @var string
     */
    protected $_endDate;

    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Filter provider
     * @var Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * Constructor
     * @param \Magento\Backend\Block\Template\Context    $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Framework\Registry                $registry
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve model
     * @return \Plumrocket\PrivateSale\Model\Emailtemplate
     */
    public function getModel()
    {
        return $this->_coreRegistry->registry('current_model');
    }

    /**
     * Retrieve list columns count
     * @return int
     */
    public function getListColumnsCount()
    {
        return $this->getModel()->getListLayout();
    }

    /**
     * Retrieve start date
     * @return string
     */
    public function getListStartDate()
    {
        if (null === $this->_startDate) {
            /** @var \Plumrocket\PrivateSale\Model\Event $event */
            foreach ($this->getModel()->getEventsCollection() as $event) {
                $date = $event->getActiveFrom();
                if (null === $this->_startDate || ($date && $date < $this->_startDate)) {
                    $this->_startDate = $date;
                }
            }
            if ($this->_startDate) {
                $_dateFormat = ($this->getModel()->getTemplateDateFormat()) ? $this->getModel()->getTemplateDateFormat() : self::DEFAULT_DATE_FORMAT;
                $this->_startDate = date($_dateFormat, strtotime($this->_startDate));
            }
        }
        return $this->_startDate;
    }

    /**
     * Retrieve list end date
     * @return string
     */
    public function getListEndDate()
    {
        if (null === $this->_endDate) {
            foreach ($this->getModel()->getEventsCollection() as $event) {
                $date = $event->getActiveTo();
                if (null === $this->_endDate || ($date && $date > $this->_endDate)) {
                    $this->_endDate = $date;
                }
            }
            if ($this->_endDate) {
                $_dateFormat = ($this->getModel()->getTemplateDateFormat()) ? $this->getModel()->getTemplateDateFormat() : self::DEFAULT_DATE_FORMAT;
                $this->_endDate = date($_dateFormat, strtotime($this->_endDate));
            }
        }
        return $this->_endDate;
    }

    /**
     * Retrieve template html
     * @return string
     */
    public function getTemplateHtml()
    {
        $_model     = $this->getModel();
        $template   = $_model->getTemplate();
        $search     = [
            '{{events_list}}',
            '{{start_date}}',
            '{{end_date}}',
            '{{title}}',
            '{{period}}',
        ];

        $replace    = [
            $this->getListHtml(),
            htmlspecialchars($this->getListStartDate()),
            htmlspecialchars($this->getListEndDate()),
            $_model->getTitle() ? htmlspecialchars($_model->getTitle()) : '*|MC:SUBJECT|*',
            $_model->getPeriodText() ? htmlspecialchars($_model->getPeriodText()) : htmlspecialchars($this->getListStartDate()) . ' - ' . htmlspecialchars($this->getListEndDate()),
        ];
        $template   = str_replace($search, $replace, $template);

        return $this->_filterProvider->getBlockFilter()->filter($template);
    }

    /**
     * Retrieve list html
     * @return string
     */
    public function getListHtml()
    {
        if (null === $this->_listHtml) {
            $_columns = (int) $this->getListColumnsCount();
            $_dateFormat = $this->getModel()->getListTemplateDateFormat()
                ? $this->getModel()->getListTemplateDateFormat()
                : self::DEFAULT_DATE_FORMAT;

            $eventCollection = $this->getModel()->getEventsCollection();
            $events = array_values($eventCollection->getItems());
            $this->_listHtml = '';
            $eventsCount = count($events);

            for ($i = 0; $i < $eventsCount; $i += $_columns) {
                $_listTemplate  = $this->getModel()->getListTemplate();

                for ($j = 0; $j < $_columns; $j++) {
                    if (isset($events[$i + $j])) {
                        /** @var \Plumrocket\PrivateSale\Api\Data\EventInterface $event */
                        $event = $events[$i + $j];
                        $eventCatalogEntity = $event->getCatalogEntity();

                        //find short name
                        $shortedName = $event->getName();

                        $pageTitle = $eventCatalogEntity && $eventCatalogEntity->getMetaTitle()
                            ? $eventCatalogEntity->getMetaTitle()
                            : $shortedName;

                        $shortPageTitle = $pageTitle;

                        switch ($_columns) {
                            case 2: $maxShortedNameLenght = 35; break;
                            case 3: $maxShortedNameLenght = 25; break;
                            default: $maxShortedNameLenght = 65; break;
                        }
                        if (mb_strlen($shortedName) > $maxShortedNameLenght) {
                            $shortedName = mb_substr($shortedName, 0, $maxShortedNameLenght) . '...';
                        }
                        if (mb_strlen($shortPageTitle) > $maxShortedNameLenght) {
                            $shortPageTitle = mb_substr($shortPageTitle, 0, $maxShortedNameLenght) . '...';
                        }

                        //find start date
                        if ($startDate = $event->getActiveFrom()) {
                            $startDate = date($_dateFormat, strtotime($startDate));
                        }

                        //find end date
                        if ($endDate = $event->getActiveTo()) {
                            $endDate = date($_dateFormat, strtotime($endDate));
                        }

                        $image = $this->getModel()->getEmailImage($event);

                        if (! $image) {
                            $image = $this->getViewFileUrl('Plumrocket_PrivateSale::images/default.jpg');
                        }

                        $data = [
                            'url'           => $eventCatalogEntity ? $eventCatalogEntity->getUrl() : null,
                            'name'          => htmlspecialchars($event->getName()),
                            'short_name'    => htmlspecialchars($shortedName),
                            'page_title'    => htmlspecialchars($pageTitle),
                            'short_page_title'  => htmlspecialchars($shortPageTitle),
                            'image'         => $image,
                            'start_date'    => htmlspecialchars($startDate),
                            'end_date'      => htmlspecialchars($endDate),
                        ];

                        $number = ($j) ? ($j + 1) : '';
                        foreach ($data as $key => $value) {
                            $_listTemplate = str_replace('{{event' . $number . '.' . $key . '}}', htmlspecialchars($value), $_listTemplate);
                        }

                        $_listTemplate = str_replace('{{n}}', $i, $_listTemplate);
                    } else {
                        $tag = '<!-- event ' . ($j + 1) . ' -->';
                        $_listTemplate = preg_replace('/' . $tag . '(.*)' . $tag . '/Uis', '', $_listTemplate);
                    }
                }
                $this->_listHtml .= $_listTemplate;
            }
        }
        return $this->_listHtml;
    }
}
