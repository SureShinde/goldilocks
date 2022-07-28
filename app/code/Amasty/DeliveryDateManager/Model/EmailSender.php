<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Model;

use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;

class EmailSender
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    public function __construct(TransportBuilder $transportBuilder)
    {
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * @param array|string $recipients
     * @param int $storeId
     * @param array $vars
     * @param string $templateIdentifier
     * @param string $sendFrom
     * @param string $area
     */
    public function execute(
        $recipients,
        int $storeId = 0,
        array $vars = [],
        string $templateIdentifier = '',
        string $sendFrom = 'general',
        string $area = Area::AREA_FRONTEND
    ): void {
        $this->transportBuilder
            ->setTemplateIdentifier($templateIdentifier)
            ->setTemplateOptions(['area' => $area, 'store' => $storeId])
            ->setTemplateVars($vars)
            ->setFromByScope($sendFrom, $storeId)
            ->addTo($recipients);

        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();
    }
}
