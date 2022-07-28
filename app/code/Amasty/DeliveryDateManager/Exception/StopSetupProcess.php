<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class StopSetupProcess extends LocalizedException
{
    /**
     * @param Phrase|null $phrase
     * @param \Exception|null $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if (!$phrase) {
            $phrase = __('Setup process has been stopped.');
        }
        parent::__construct($phrase, $cause, (int) $code);
    }
}
