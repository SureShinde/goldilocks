<?php

declare(strict_types=1);

namespace Amasty\Preorder\Model\Product\Inventory;

use Amasty\Preorder\Model\ResourceModel\Product\Inventory\LoadSourceCodes;

class GetSourceCodes
{
    /**
     * @var array
     */
    private $sourceCodes = [];

    /**
     * @var LoadSourceCodes
     */
    private $loadSourceCodes;

    public function __construct(LoadSourceCodes $loadSourceCodes)
    {
        $this->loadSourceCodes = $loadSourceCodes;
    }

    public function execute(string $websiteCode): array
    {
        if (!isset($this->sourceCodes[$websiteCode])) {
            $this->sourceCodes[$websiteCode] = $this->loadSourceCodes->execute($websiteCode);
        }

        return $this->sourceCodes[$websiteCode];
    }
}
