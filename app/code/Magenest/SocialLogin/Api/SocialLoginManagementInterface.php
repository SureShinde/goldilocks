<?php
/**
 * Copyright © SocialLoginRestApi All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\SocialLogin\Api;

interface SocialLoginManagementInterface
{

    /**
     * POST for SocialLogin api
     * @return string
     */
    public function postSocialLogin();
}

