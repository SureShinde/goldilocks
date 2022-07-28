<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Model\Source;

use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Data\OptionSourceInterface;

class EmailTemplate implements OptionSourceInterface
{
    /**
     * @var string
     */
    private $origTemplateCode;

    /**
     * @var CollectionFactory
     */
    private $templateCollectionFactory;

    /**
     * @var Config
     */
    private $emailConfig;

    public function __construct(
        CollectionFactory $templateCollectionFactory,
        Config $emailConfig,
        string $origTemplateCode = ''
    ) {
        $this->origTemplateCode = $origTemplateCode;
        $this->templateCollectionFactory = $templateCollectionFactory;
        $this->emailConfig = $emailConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->templateCollectionFactory->create()
            ->addFieldToFilter('orig_template_code', ['eq' => $this->origTemplateCode]);

        $options = $collection->toOptionArray();
        array_unshift($options, $this->getDefaultTemplate());

        return $options;
    }

    private function getDefaultTemplate(): array
    {
        $templateLabel = $this->emailConfig->getTemplateLabel($this->origTemplateCode);
        $templateLabel = __('%1 (Default)', $templateLabel);

        return ['value' => $this->origTemplateCode, 'label' => $templateLabel];
    }
}
