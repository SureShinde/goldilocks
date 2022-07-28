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

namespace Plumrocket\PrivateSale\Helper;

use Magento\Framework\App\ResourceConnection;

class Preview extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Date
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Resource
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * Preview key
     * @var string
     */
    protected $previewKey = 'psPreviewMode';

    /**
     * @var bool
     */
    private $allowPreview;

    /**
     * @var bool
     */
    private $canChangeData = true;

    /**
     * @var \Plumrocket\PrivateSale\Helper\Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param ResourceConnection                          $resource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\Helper\Context       $context
     * @param \Plumrocket\PrivateSale\Helper\Config       $config
     */
    public function __construct(
        ResourceConnection $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Helper\Context $context,
        Config $config
    ) {
        $this->resource = $resource;
        $this->date = $date;
        parent::__construct($context);
        $this->config = $config;
    }

    /**
     * Retrieve preview key
     * @return string
     */
    public function getPreviewKey()
    {
        return $this->previewKey;
    }

    /**
     * Checking preview accessible
     * If isset preview key and code is valid
     * @return boolean
     */
    public function isAllow(): bool
    {
        if (null !== $this->allowPreview) {
            return $this->allowPreview;
        }

        $request = $this->_request;

        if ($request->isXmlHttpRequest()
            && $request->getFullActionName() === 'prprivatesale_ajax_timeAction'
            && $request->getParam('previewDate')
            && $request->getParam($this->getPreviewKey())
        ) {
            return true;
        }

        $ssd = $request->getParam($this->getPreviewKey());

        if (!$ssd) {
            return false;
        }

        $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        $data = $connection
            ->fetchAll(sprintf(
                "SELECT `code` FROM %s WHERE `code` = %s AND `active_to` >= '%s'",
                $this->resource->getTableName('plumrocket_privatesale_preview_access'),
                $connection->quote($ssd),
                strftime('%F %T', $this->date->timestamp(time()))
            ));

        if ($data) {
            return $this->allowPreview = true;
        }

        return $this->allowPreview = false;
    }

    /**
     * Retrieve preview time
     * @return int
     */
    public function getPreviewTime()
    {
        return strtotime($this->getPreviewDate());
    }

    /**
     * Retrieve preview date
     * @return string
     */
    public function getPreviewDate()
    {
        if (!$previewDate = $this->_request->getParam('previewDate')) {
            $previewDate = $this->date->gmtDate('m/d/Y');
        }

        return $previewDate;
    }

    /**
     * @return string|null
     */
    public function getPreviewMode()
    {
        return $this->_request->getParam($this->previewKey);
    }

    /**
     * Preview mode change a lot of information. Some plugins and observers might cause loops.
     * You can use this method to avoid them.
     *
     * To manage permissions of changing data use:
     * @see continuePreviewInfluence
     * @see pausePreviewInfluence
     *
     * @return bool
     */
    public function isAllowToChangeData()
    {
        return $this->canChangeData && $this->config->isModuleEnabled() && $this->isAllow();
    }

    /**
     * @return $this
     */
    public function pausePreviewInfluence()
    {
        $this->canChangeData = false;
        return $this;
    }

    /**
     * @return $this
     */
    public function continuePreviewInfluence()
    {
        $this->canChangeData = true;
        return $this;
    }
}
