<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Block\Component;

use Amasty\DeliveryDateManager\Block\Checkout\LayoutProcessor;
use Amasty\DeliveryDateManager\Model\ConfigProvider;

class CommentComponent implements ComponentInterface
{
    public const NAME = 'deliverydate_comment';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getComponent(int $storeId): array
    {
        $validation = [
            'required-entry' => $this->configProvider->isCommentRequired($storeId)
        ];

        $maxLength = $this->configProvider->getCommentMaxLength($storeId);
        if ($maxLength) {
            $validation['max_text_length'] = $maxLength;
        }

        return [
            'component' => 'Magento_Ui/js/form/element/textarea',
            'label' => __('Delivery Comments'),
            'sortOrder' => 202,
            'validation' => $validation,
            'dataScope' => 'amdeliverydate_comment',
            'provider' => 'checkoutProvider',
            'notice' => $this->configProvider->getCommentNote($storeId),
            'visible' => true,
            'isRequired' => $this->configProvider->isCommentRequired($storeId),
            'config' => [
                'template' => 'ui/form/field',
                'cols' => 5,
                'rows' => 5,
                'elementTmpl' => 'Amasty_DeliveryDateManager/form/element/textarea',
                'storageConfig' => [
                    'provider' => 'sectionLocalStorage',
                    'namespace' => LayoutProcessor::STORAGE_SECTION_NAME . '.' . '${$.dataScope}'
                ],
                'statefull' => ['value' => true]
            ]
        ];
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isEnabled(int $storeId): bool
    {
        return $this->configProvider->isCommentEnabled($storeId);
    }
}
