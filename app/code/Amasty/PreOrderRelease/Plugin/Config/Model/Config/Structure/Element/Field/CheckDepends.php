<?php

declare(strict_types=1);

namespace Amasty\PreOrderRelease\Plugin\Config\Model\Config\Structure\Element\Field;

use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CheckDepends
{
    public const SCOPE_DEFAULT = 'default';
    public const SCOPE_STORE = 'store';
    public const RELEASE_DATE_GROUP = 'ampreorder/release_date';
    public const HELPER_FIELD = 'fix_for_scope';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function beforeSetData(Field $subject, array $data, string $scope): array
    {
        if ($scope !== self::SCOPE_DEFAULT && isset($data['path']) && $data['path'] === self::RELEASE_DATE_GROUP) {
            $depends = $data['depends']['fields'] ?? [];
            $hideByDepends = true;
            foreach ($depends as $fieldName => $fieldConfig) {
                $value = $this->scopeConfig->getValue($fieldConfig['id']);
                $hideByDepends = $value != $fieldConfig['value'];
                if ($hideByDepends) {
                    break;
                }
            }
            if ($hideByDepends) {
                $data['depends']['fields'] = [
                    self::HELPER_FIELD => [
                        'id' => sprintf('%s/%s', self::RELEASE_DATE_GROUP, self::HELPER_FIELD),
                        'value' => 1,
                        'negative'=> $scope !== self::SCOPE_STORE
                    ]
                ];
            } else {
                unset($data['depends']);
            }
        }

        return [$data, $scope];
    }
}
