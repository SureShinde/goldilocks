<?php

declare(strict_types=1);

namespace Amasty\Preorder\Plugin\Sales\Model\Order\Item;

use Amasty\Preorder\Model\Order\Item\GetPreorderInformation;
use Amasty\Preorder\Model\Utils\StripTags;
use Magento\Sales\Model\Order\Item;

class ModifyName
{
    /**
     * @var GetPreorderInformation
     */
    private $getPreorderInformation;

    /**
     * @var StripTags
     */
    private $stripTags;

    public function __construct(
        GetPreorderInformation $getPreorderInformation,
        StripTags $stripTags
    ) {
        $this->getPreorderInformation = $getPreorderInformation;
        $this->stripTags = $stripTags;
    }

    public function afterGetName(Item $subject, ?string $result): ?string
    {
        if ($result !== null) {
            $preorderInformation = $this->getPreorderInformation->execute($subject);
            if ($preorderInformation->isPreorder()) {
                $note = $this->stripTags->execute($preorderInformation->getNote());
                if (stripos($result, $note) === false) {
                    $result .= ' ' . $note;
                }
            }
        }

        return $result;
    }
}
