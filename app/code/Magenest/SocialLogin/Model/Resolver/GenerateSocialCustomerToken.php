<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\SocialLogin\Model\Resolver;

use GraphQL\Error\Error;
use Magenest\SocialLogin\Helper\SocialLogin;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;

class GenerateSocialCustomerToken implements ResolverInterface
{

    /**
     * @var DataProvider\GenerateSocialCustomerToken
     */
    private $generateSocialCustomerTokenDataProvider;

    /**
     * @var OauthHelper
     */
    private $oauthHelper;

    protected $socialHelper;

    /**
     * @param DataProvider\GenerateSocialCustomerToken $generateSocialCustomerTokenDataProvider
     * @param SocialLogin $socialHelper
     * @param OauthHelper $oauthHelper
     */
    public function __construct(
        DataProvider\GenerateSocialCustomerToken $generateSocialCustomerTokenDataProvider,
        SocialLogin $socialHelper,
        OauthHelper $oauthHelper
    ) {
        $this->generateSocialCustomerTokenDataProvider = $generateSocialCustomerTokenDataProvider;
        $this->oauthHelper                             = $oauthHelper;
        $this->socialHelper = $socialHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        if (!$this->socialHelper->getAllTypeOptionsAcceptedForApi($args["type"])) {
            throw new GraphQlAuthenticationException(__('type should be only ["facebook","google","amazon","instagram","line","linkedin","reddit","twitter","zalo","apple"]'));
        }

        try {
            $returnData["expired_after_hours"] = $this->oauthHelper->getCustomerTokenLifetime();

            $token      = $this->generateSocialCustomerTokenDataProvider->getGenerateSocialCustomerToken($args["identifier"], $args["type"]);
            $returnData = array_merge($token, $returnData);

            return $returnData;
        } catch (\Exception $e) {
            throw new GraphQlAuthenticationException(__($e->getMessage()));
        }
    }
}

