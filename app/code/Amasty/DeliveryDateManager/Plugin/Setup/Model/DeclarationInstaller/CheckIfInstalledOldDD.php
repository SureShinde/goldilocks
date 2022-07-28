<?php
declare(strict_types=1);

namespace Amasty\DeliveryDateManager\Plugin\Setup\Model\DeclarationInstaller;

use Amasty\DeliveryDateManager\Exception\StopSetupProcess;
use Magento\Framework\Module\Manager;
use Magento\Setup\Model\DeclarationInstaller;

class CheckIfInstalledOldDD
{
    public const OLD_DD_NAME = 'Amasty_Deliverydate';

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param DeclarationInstaller $subject
     * @param array $requestData
     * @return null
     * @throws StopSetupProcess
     */
    public function beforeInstallSchema(DeclarationInstaller $subject, array $requestData)
    {
        if ($this->moduleManager->isEnabled(self::OLD_DD_NAME)) {
            throw new StopSetupProcess(
                __(
                    'Legacy version of Delivery Date detected.' . "\n" .
                    'Extension configuration will not be transferred from Legacy to current extension version - ' .
                    'so backup relevant data as necessary prior to advancing with extension install.' . "\n" .
                    'Please run ‘composer remove amasty/delivery-date’ to uninstall Composer based version of ' .
                    'legacy extension - or remove app/code/Amasty/Deliverydate folder in case of non-Composer ' .
                    'based install. Then, restart extension’s current version install process.'
                )
            );
        }

        return null;
    }
}
