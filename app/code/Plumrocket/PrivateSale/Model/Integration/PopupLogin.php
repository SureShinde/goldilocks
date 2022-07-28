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
 * @package     Plumrocket Private Sales and Flash Sales
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\PrivateSale\Model\Integration;

use Plumrocket\PrivateSale\Api\Data\EventInterface;
use Plumrocket\PrivateSale\Model\Config\Source\Eventlandingpage;

class PopupLogin extends Loader
{
    /**
     * @return bool
     */
    public function isReady(): bool
    {
        /** @var \Plumrocket\Popuplogin\Helper\Config|\Plumrocket\Popuplogin\Helper\Data $model */
        $model = $this->getLoadedModel();

        if (! empty($model)) {
            if (! method_exists($model, 'isEnabledOnAnyForm')) {
                return (bool) $model->moduleEnabled();
            }
            return $model->isEnabledOnAnyForm();
        }

        return false;
    }

    /**
     * @param EventInterface $event
     * @return bool
     */
    public function isActive($event): bool
    {
        if ($this->isReady()) {
            $landingPage = $event->getPrivateSaleLandingPage();

            if ($landingPage === (string) Eventlandingpage::LOGIN_PAGE
                || $landingPage === (string) Eventlandingpage::REGISTRATION_PAGE
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|int $landingPage
     * @return string
     */
    public function getFormType($landingPage): string
    {
        if ($this->isReady()) {
            if (is_numeric($landingPage)) {
                if ((int) $landingPage === Eventlandingpage::REGISTRATION_PAGE) {
                    return 'prpl-registration';
                }

                if ((int) $landingPage === Eventlandingpage::LOGIN_PAGE) {
                    return 'prpl-login';
                }
            }
        }

        return '';
    }
}
