<?php
/**
 * Copyright © eComBricks. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace Ecombricks\StoreInventory\Model\InventorySalesAdminUi\OptionSource;

/**
 * Store source
 */
class StoreSource implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * Tab length
     */
    const TAB_LENGTH = 4;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * Options
     *
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Escaper $escaper
     * @return void
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Escaper $escaper
    )
    {
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
    }

    /**
     * Get label
     *
     * @param string $name
     * @param int|null $indent
     * @return string
     */
    protected function getLabel(string $name, $indent = 0): string
    {
        $label = $this->escaper->escapeHtml($name);
        return $indent > 0 ? str_repeat(' ', $indent * static::TAB_LENGTH).$label : $label;
    }

    /**
     * Get option
     *
     * @param string $label
     * @param array|string $value
     * @return array
     */
    protected function getOption(string $label, $value): array
    {
        return ['label' => $label, 'value' => $value];
    }

    /**
     * Get options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $options = [];
        $websites = $this->storeManager->getWebsites();
        $groups = $this->storeManager->getGroups();
        $stores = $this->storeManager->getStores();
        foreach ($websites as $website) {
            $websiteValues = [];
            foreach ($groups as $group) {
                $groupValues = [];
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                foreach ($stores as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    $groupValues[] = $this->getOption($this->getLabel($store->getName(), 2), $store->getCode());
                }
                if (!empty($groupValues)) {
                    $websiteValues[] = $this->getOption($this->getLabel($group->getName(), 1), $groupValues);
                }
            }
            if (!empty($websiteValues)) {
                $options[] = $this->getOption($this->getLabel($website->getName(), 0), $websiteValues);
            }
        }
        return $options;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }
        $this->options = $this->getOptions();
        return $this->options;
    }

}
