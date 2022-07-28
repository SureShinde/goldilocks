<?php
namespace Magenest\SocialLogin\Model\Reddit;

use Magenest\SocialLogin\Model\AbstractClient;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Client
 * @package Magenest\SocialLogin\Model\Reddit
 */
class Client extends AbstractClient
{
    /**
     *
     */
    const CHART_COLOR = '#28a3e9';
    /**
     *
     */
    const TYPE_SOCIAL_LOGIN = 'reddit';
    /**
     * @var string
     */
    protected $redirect_uri_path  = 'sociallogin/reddit/connect';
    /**
     * @var string
     */
    protected $path_enalbed       ='magenest/credentials/reddit/enabled';
    /**
     * @var string
     */
    protected $path_client_id     ='magenest/credentials/reddit/client_id';
    /**
     * @var string
     */
    protected $path_client_secret ='magenest/credentials/reddit/client_secret';
    /**
     * @var string
     */
    protected $oauth2_service_uri = 'https://oauth.reddit.com/api/v1';
    /**
     * @var string
     */
    protected $oauth2_auth_uri  ='https://www.reddit.com/api/v1/authorize';
    /**
     * @var string
     */
    protected $oauth2_token_uri = 'https://www.reddit.com/api/v1/access_token';
    /**
     * @var string[]
     */
    protected $scope = [
        'identity'
    ];

    /**
     * @return string
     */
    public function createAuthUrl()
    {
        $query = [
            'client_id' => $this->getClientId(),
            'response_type' => 'code',
            'state' => 'access',
            'redirect_uri' => $this->getRedirectUri(),
            'duration' => 'temporary',
            'scope' => implode(' ', $this->getScope())
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

        $method = strtoupper($method);
        $params = array_merge([
            'access_token' => $this->token
        ], $params);

        $headers = ['Authorization'=>'Bearer '.$this->token];
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
            'code' => $code,
            'redirect_uri' => $this->getRedirectUri() ];
        if (empty($code)) {
            throw new LocalizedException(__('Unable to retrieve access code.'));
        }
        $headers = ['Authorization'=>'Basic '.base64_encode($this->getClientId().':'.$this->getClientSecret())];
        $response = $this->_httpRequest(
            $this->oauth2_token_uri,
            'POST',
            $token_array,
            $headers
        );
        $this->setAccessToken($response['access_token']);
        return $this->getAccessToken();
    }
}
