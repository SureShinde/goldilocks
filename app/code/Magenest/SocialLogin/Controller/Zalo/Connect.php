<?php
namespace Magenest\SocialLogin\Controller\Zalo;
use Magenest\SocialLogin\Controller\AbstractConnect;
use Zalo\ZaloEndPoint;

/**
 * Class Connect
 *
 * @package Magenest\SocialLogin\Controller\Zalo
 */
class Connect extends AbstractConnect
{
    /**
     * @var string
     */
    protected $_exeptionMessage = 'Zalo login failed.';

    /**
     * @var string
     */
    protected $_type = 'zalo';

    /**
     * @var string
     */
    protected $_path = ZaloEndPoint::API_GRAPH_ME;

    /**
     * @var string
     */
    protected $clientModel = '\Magenest\SocialLogin\Model\Zalo\Client';

    /**
     * @param $userInfo
     * @return array
     */
    public function getDataNeedSave($userInfo)
    {
        $dataParent = parent::getDataNeedSave($userInfo);
        $fullName = $this->split_name($userInfo['name']);

        $data = [
            'email' => $userInfo['email'],
            'firstname' => $fullName['first_name'],
            'lastname' => $fullName['last_name'],
        ];

        return array_replace_recursive($dataParent, $data);
    }

    /**
     * @param $name
     *
     * @return array
     */
    public function split_name($name) {
	    $name = explode(' ', trim($name), 2);
	    if (count($name) > 1) {
		    $firstName = $name[0];
		    $lastName = $name[1];
	    } else {
		    $firstName = $name[0];
		    $lastName = $name[0];
	    }
        return ['first_name' => $firstName, 'last_name' => $lastName];
    }

    /**
     * @param $client
     * @param $code
     */
    public function getUserInfo($client, $code)
    {
        $userInfo = $client->api($this->_path, $code);
        return $userInfo;
    }
}
