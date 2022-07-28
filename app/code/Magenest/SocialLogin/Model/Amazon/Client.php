<?php
namespace Magenest\SocialLogin\Model\Amazon;

use Magenest\SocialLogin\Model\AbstractClient;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Client
 * @package Magenest\SocialLogin\Model\Amazon
 */
class Client extends AbstractClient
{
    /**
     *
     */
    const CHART_COLOR = '#d85a1e';
    /**
     *
     */
    const TYPE_SOCIAL_LOGIN = 'amazon';
    /**
     * @var string
     */
    protected $redirect_uri_path  = 'sociallogin/amazon/connect';
    /**
     * @var string
     */
    protected $path_enalbed       ='magenest/credentials/amazon/enabled';
    /**
     * @var string
     */
    protected $path_client_id     ='magenest/credentials/amazon/client_id';
    /**
     * @var string
     */
    protected $path_client_secret ='magenest/credentials/amazon/client_secret';
    /**
     * @var string
     */
    protected $oauth2_service_uri = 'https://api.amazon.com';
    /**
     * @var string
     */
    protected $oauth2_auth_uri  ='https://www.amazon.com/ap/oa';
    /**
     * @var string
     */
    protected $oauth2_token_uri = 'https://api.amazon.com/auth/o2/token';
    /**
     * @var string[]
     */
    protected $scope = [
        'profile'
    ];

    /**
     * @return string
     */
    public function createAuthUrl()
    {
        $query = [
            'client_id' => $this->getClientId(),
            'scope' => implode(' ', $this->getScope()),
            'response_type' => 'code',
            'state' => $this->getState(),
            'redirect_uri' => $this->getRedirectUri(),
        ];
        $url = $this->oauth2_auth_uri . '?' . http_build_query($query);
        return $url;
    }


    /**
     * @param null $code
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    protected function fetchAccessToken($code = null)
    {
        $token_array = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->getRedirectUri(),
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret()
        ];
        if (empty($code)) {
            throw new LocalizedException(
                __('Unable to retrieve access code.')
            );
        }
        $response = $this->_httpRequest(
            $this->oauth2_token_uri,
            'POST',
            $token_array
        );
        $this->setAccessToken($response['access_token']);
        return $this->getAccessToken();
    }
}
