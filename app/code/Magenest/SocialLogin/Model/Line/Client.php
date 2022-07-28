<?php
namespace Magenest\SocialLogin\Model\Line;

use Magenest\SocialLogin\Model\AbstractClient;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Client
 * @package Magenest\SocialLogin\Model\Line
 */
class Client extends AbstractClient
{
    /**
     *
     */
    const CHART_COLOR = '#4cb234';
    /**
     *
     */
    const TYPE_SOCIAL_LOGIN = 'line';
    /**
     * @var string
     */
    protected $redirect_uri_path  = 'sociallogin/line/connect';
    /**
     * @var string
     */
    protected $path_enalbed       = 'magenest/credentials/line/enabled';
    /**
     * @var string
     */
    protected $path_client_id     = 'magenest/credentials/line/client_id';
    /**
     * @var string
     */
    protected $path_client_secret = 'magenest/credentials/line/client_secret';
    /**
     * @var string
     */
    protected $oauth2_service_uri = 'https://api.line.me';
    /**
     * @var string
     */
    protected $oauth2_auth_uri  = 'https://access.line.me/oauth2/v2.1/authorize';
    /**
     * @var string
     */
    protected $oauth2_token_uri = 'https://api.line.me/oauth2/v2.1/token';
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
            'response_type' => 'code',
            'client_id' => $this->getClientId(),
            'redirect_uri' => $this->getRedirectUri(),
            'state' => $this->generateRandomString(),
            'scope' => implode(',', $this->getScope()),
        ];
        $url = $this->oauth2_auth_uri . '?' . http_build_query($query);
        return $url;
    }

    /**
     * @param $endpoint
     * @param $code
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    public function api($endpoint, $code, $method = 'GET', $params = [])
    {
        if (empty($this->token)) {
            $this->fetchAccessToken($code);
        }
        $url = $this->oauth2_service_uri . $endpoint;
        $headers = ['Authorization' => 'Bearer ' . $this->token];
        $response = $this->_httpRequest($url, $method, $params, $headers);
        return $response;
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
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'code' => $code,
            'redirect_uri' => $this->getRedirectUri(),
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

    /**
     * @return string
     */
    protected function generateRandomString()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 6; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
