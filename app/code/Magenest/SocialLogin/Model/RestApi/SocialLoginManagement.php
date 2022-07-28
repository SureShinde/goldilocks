<?php
/**
 * Copyright © SocialLoginRestApi All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\SocialLogin\Model\RestApi;

use Magenest\SocialLogin\Api\SocialLoginManagementInterface;
use Magenest\SocialLogin\Model\Google\Client;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Integration\Helper\Oauth\Data as OauthHelper;
use Magento\Store\Model\StoreManagerInterface;
use Vertex\Exception\ApiException;

class SocialLoginManagement implements SocialLoginManagementInterface
{
    const XML_PATH_ENABLE_CONFIRMATION_REQUIRED = 'magenest/credentials/%s/is_confirmation_required';

    protected $_google;

    protected $socialHelper;

    /**
     * @type StoreManagerInterface
     */
    protected $storeManager;

    protected $tokenModelFactory;

    /**
     * @var \Magenest\SocialLogin\Model\Facebook\Client
     */
    protected $_facebook;

    /**
     * @var \Magenest\SocialLogin\Model\Amazon\Client
     */
    protected $_amazon;

    /**
     * @var \Magenest\SocialLogin\Model\Apple\Client
     */
    protected $_apple;

    /**
     * @var \Magenest\SocialLogin\Model\Instagram\Client
     */
    protected $_instagram;

    /**
     * @var \Magenest\SocialLogin\Model\Line\Client
     */
    protected $_line;

    /**
     * @var \Magenest\SocialLogin\Model\Linkedin\Client
     */
    protected $_linkedin;

    /**
     * @var \Magenest\SocialLogin\Model\Pinterest\Client
     */
    protected $_pinterest;

    /**
     * @var \Magenest\SocialLogin\Model\Reddit\Client
     */
    protected $_reddit;

    /**
     * @var \Magenest\SocialLogin\Model\Twitter\Client
     */
    protected $_twitter;

    /**
     * @var \Magenest\SocialLogin\Model\Zalo\Client
     */
    protected $_zalo;

    /**
     * @var string
     */
    protected $_path = '/userinfo';

    protected $_type;

    protected $_customerSession;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var SessionManagerInterface
     */
    protected $socialSession;

    protected $_request;

    protected $oauthHelper;

    /**
     * CreateCustomer constructor.
     * @param Http $request
     * @param Client $socialApi
     * @param \Magenest\SocialLogin\Helper\SocialLogin $socialHelper
     * @param StoreManagerInterface $storeManager
     * @param Session $customerSession
     * @param SessionManagerInterface $sessionManager
     * @param SessionManagerInterface $socialSession
     * @param \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory
     * @param \Magenest\SocialLogin\Model\Facebook\Client $socialFacebookModel
     * @param \Magenest\SocialLogin\Model\Amazon\Client $socialAmazonModel
     * @param \Magenest\SocialLogin\Model\Apple\Client $socialAppleModel
     * @param \Magenest\SocialLogin\Model\Instagram\Client $socialInstagramModel
     * @param \Magenest\SocialLogin\Model\Line\Client $socialLineModel
     * @param \Magenest\SocialLogin\Model\Linkedin\Client $socialLinkedinModel
     * @param \Magenest\SocialLogin\Model\Pinterest\Client $socialPinterestModel
     * @param \Magenest\SocialLogin\Model\Reddit\Client $socialRedditModel
     * @param \Magenest\SocialLogin\Model\Twitter\Client $socialTwitterModel
     * @param \Magenest\SocialLogin\Model\Zalo\Client $socialZaloModel
     */
    public function __construct(
        Http $request,
        \Magenest\SocialLogin\Model\Google\Client $socialApi,
        \Magenest\SocialLogin\Helper\SocialLogin $socialHelper,
        StoreManagerInterface $storeManager,
        Session                 $customerSession,
        SessionManagerInterface $sessionManager,
        SessionManagerInterface $socialSession,
        OauthHelper $oauthHelper,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory,
        \Magenest\SocialLogin\Model\Facebook\Client $socialFacebookModel,
        \Magenest\SocialLogin\Model\Amazon\Client $socialAmazonModel,
        \Magenest\SocialLogin\Model\Apple\Client $socialAppleModel,
        \Magenest\SocialLogin\Model\Instagram\Client $socialInstagramModel,
        \Magenest\SocialLogin\Model\Line\Client $socialLineModel,
        \Magenest\SocialLogin\Model\Linkedin\Client $socialLinkedinModel,
        \Magenest\SocialLogin\Model\Pinterest\Client $socialPinterestModel,
        \Magenest\SocialLogin\Model\Reddit\Client $socialRedditModel,
        \Magenest\SocialLogin\Model\Twitter\Client $socialTwitterModel,
        \Magenest\SocialLogin\Model\Zalo\Client $socialZaloModel
    ) {
        $this->_request = $request;
        $this->socialHelper = $socialHelper;
        $this->_google = $socialApi;
        $this->storeManager = $storeManager;
        $this->tokenModelFactory = $tokenModelFactory;
        $this->_facebook = $socialFacebookModel;
        $this->_amazon = $socialAmazonModel;
        $this->_instagram = $socialInstagramModel;
        $this->_line = $socialLineModel;
        $this->_linkedin = $socialLinkedinModel;
        $this->_pinterest = $socialPinterestModel;
        $this->_reddit = $socialRedditModel;
        $this->_twitter = $socialTwitterModel;
        $this->_zalo = $socialZaloModel;
        $this->_apple = $socialAppleModel;
        $this->_customerSession = $customerSession;
        $this->sessionManager = $sessionManager;
        $this->socialSession = $socialSession;
        $this->oauthHelper = $oauthHelper;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function postSocialLogin()
    {
        $customerInfo = $this->_request->getPost();
        if (!$this->socialHelper->getAllTypeOptionsAcceptedForApi($customerInfo["type"])) {
            throw new \Magento\Framework\Webapi\Exception(__('type should be only ["facebook","google","amazon","instagram","line","linkedin","reddit","twitter","zalo","apple"]'));
        }
        try {
            $returnData["expired_after_hours"] = $this->oauthHelper->getCustomerTokenLifetime();
            $token      = $this->getGenerateSocialCustomerToken($customerInfo["identifier"], $customerInfo["type"]);
            $returnData = array_merge($token, $returnData);

            return $returnData;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Webapi\Exception(__($e->getMessage()), 101);
        }
    }
    /**
     * @param $identifier
     * @param $type
     *
     * @return null[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGenerateSocialCustomerToken($identifier, $type)
    {
        $fillType = '_' . $type;
        $isEnable = $this->$fillType->isEnabled();
        if ($isEnable) {
            $customer = $this->getSocialUser($identifier, $type);
        }
        $token = null;
        if ($customer->getId()) {
            $token = $this->tokenModelFactory->create()->createCustomerToken($customer->getId())->getToken();
        }
        return ['token' => $token];
    }

    protected function getSocialUser($identifier, $type)
    {
        $fillType = "_" . strtolower($type);
        $clientId = $this->$fillType->getClientId();
        $clientSecret = $this->$fillType->getClientSecret();
        if (!$clientId || !$clientSecret) {
            throw new \Magento\Framework\Webapi\Exception(__('Cannot connect to ' . $type), 101);
        }

        $userInfo = $this->$fillType->api($this->_path, $identifier);
        if (!isset($userInfo['email'])) {
            $userInfo['exist_email'] = 0;
        } else {
            $userInfo['exist_email'] = 1;
        }

        $user = [
            'email'      => $userInfo['email'] ?: $userInfo['id'] . '@' . "google" . '.com',
            'lastname'   => $userInfo['family_name'] ?: (array_shift($name) ?: $userInfo['id']),
            'firstname'  => $userInfo['given_name'] ?: (array_shift($name) ?: $userInfo['id']),
            'identifier' => $userInfo['id'],
            'type'       => $type,
            'password'   => isset($userProfile->password) ? $userProfile->password : null,
            'social_login_id'=>$userInfo['id'],
            'exist_email' => $userInfo["exist_email"]
        ];

        return $this->prepareCustomer($user, $type);
    }

    /**
     * @param $user
     *
     * @return \Magento\Customer\Model\Customer|mixed
     * @throws ApiException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function prepareCustomer($user, $type)
    {
        $customer = $this->socialHelper->getCustomerByEmail($user['email']);

        if ($customer->getId()) {
            if ($type == "apple" && !isset($user['is_private_email'])) {
                if ($user['email'] != $customer->getEmail() && isset($user['email_verified'])) {
                    $customer->setEmail($user['email']);
                    $customer->save();
                }
            }

            if (
            $this->socialHelper->checkConditionConfirmationForSocialLoginFromSecondTime(
                sprintf(self::XML_PATH_ENABLE_CONFIRMATION_REQUIRED, $type),
                $customer
            )
            ) {
                throw new \Magento\Framework\Webapi\Exception(__('You must confirm your account. Please check your email for the confirmation link. '), 101);
            }
            $data = [
                "id" => $user["social_login_id"],
                "type" => $type,
                "exist_email" => $user["exist_email"]
            ];

            $this->_customerSession->setCustomerAsLoggedIn($customer);
            $this->socialHelper->updateLastLoginTime($data);
            $this->socialSession->setSocialType($type ?? '');
            $this->socialHelper->getCustomer($user['social_login_id'], $type);
        } else {
            try {
                $customer = $this->socialHelper->creatingAccount(
                    $user,
                    sprintf(self::XML_PATH_ENABLE_CONFIRMATION_REQUIRED, $type)
                );
                $data = [
                    "id" => $user["social_login_id"],
                    "customerId" => $customer->getId(),
                    "type" => $type,
                    "email" => $user["email"],
                    "exist_email" => $user["exist_email"]
                ];
                $this->socialHelper->createSocialAccount($data);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Webapi\Exception(__('Email is Null, Please enter email in your %1 profile', $e->getMessage()));
            }
        }

        return $customer;
    }
}
