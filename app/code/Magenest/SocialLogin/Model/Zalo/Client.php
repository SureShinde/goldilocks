<?php
namespace Magenest\SocialLogin\Model\Zalo;

use Magenest\SocialLogin\Model\AbstractClient;
use Magento\Backend\App\ConfigInterface;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\UrlInterface;
use Zalo\Zalo;

/**
 * Class Client
 *
 * @package Magenest\SocialLogin\Model\Zalo
 */
class Client extends AbstractClient
{
    /**
     *
     */
    const CHART_COLOR = '#176eab';
    /**
     *
     */
    const TYPE_SOCIAL_LOGIN = 'zalo';
    /**
     * @var string
     */
    protected $redirect_uri_path  = 'sociallogin/zalo/connect';
    /**
     * @var string
     */
    protected $path_enalbed       ='magenest/credentials/zalo/enabled';
    /**
     * @var string
     */
    protected $path_client_id     ='magenest/credentials/zalo/client_id';
    /**
     * @var string
     */
    protected $path_client_secret ='magenest/credentials/zalo/client_secret';
    /**
     * @var string
     */
    protected $redirectUri;
    /**
     * @var \Zalo\Zalo
     */
    protected $client;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_config;

	/**
	 * Client constructor.
	 *
	 * @param ZendClientFactory $httpClientFactory
	 * @param ConfigInterface $config
	 * @param UrlInterface $url
	 * @throws \Zalo\Exceptions\ZaloSDKException
	 */
	public function __construct(
		ZendClientFactory $httpClientFactory,
		ConfigInterface $config,
		UrlInterface $url
	)
	{
		parent::__construct($httpClientFactory, $config, $url);
		$this->_construct();
	}

    /**
     *
     */
    private function _construct(){
		try {
			if ($this->isEnabled()) {
				$configZalo   = [
					'app_id'       => $this->getClientId(),
					'app_secret'   => $this->getClientSecret(),
					'callback_url' => $this->redirectUri
				];
				$this->client = new Zalo($configZalo);
			}
		}catch (\Exception $exception){
		}
	}


    /**
     * @return string|void
     */
    public function createAuthUrl()
    {
        $helper = $this->client->getRedirectLoginHelper();
        $url = $helper->getLoginUrl($this->redirectUri);
        return $url;
    }

    /**
     * @param null $code
     *
     * @throws \Zalo\Exceptions\ZaloSDKException
     */
    public function fetchAccessToken($code = null)
    {
        $helper = $this->client->getRedirectLoginHelper();
        $accessToken = $helper->getAccessToken($this->redirectUri);
        $this->setAccessToken($accessToken);
    }

    /**
     * @param        $endpoint
     * @param        $code
     * @param string $method
     * @param array  $params
     *
     * @return array|mixed
     * @throws \Zalo\Exceptions\ZaloSDKException
     */
    public function api($endpoint, $code, $method = 'GET', $params = [])
    {
        if(empty($this->token)){
            $this->fetchAccessToken($code);
        }
        $params = ['fields' => 'id,name,gender'];
        $response = $this->client->get($endpoint, $this->token, $params);
        $result = $response->getDecodedBody();
        return $result;
    }
}
