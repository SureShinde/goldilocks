<?php
namespace Magenest\SocialLogin\Controller\Twitter;

use Magenest\SocialLogin\Controller\AbstractConnect;

/**
 * Class Connect
 * @package Magenest\SocialLogin\Controller\Twitter
 */
class Connect extends AbstractConnect
{
    /**
     * @var string
     */
    protected $_type       = 'twitter';
    /**
     * @var string
     */
    protected $clientModel = '\Magenest\SocialLogin\Model\Twitter\Client';

    /**
     * @param $client
     * @param $code
     * @return array|void
     */
    public function getUserInfo($client, $code)
    {
        $getParams = $this->getRequest()->getParams();
        return array_merge(
            (array)($userInfo = $client->api('/account/verify_credentials.json', $getParams, 'GET', ['skip_status' => true])),
            ['email' => sprintf('%s@twitter-user.com', strtolower($userInfo['screen_name']))]
        );
    }

    /**
     * @param $userInfo
     * @return array
     */
    public function getDataNeedSave($userInfo)
    {
        $name = explode(' ', $userInfo['name'], 2);
        if (count($name) > 1) {
            $firstName = $name[0];
            $lastName = $name[1];
        } else {
            $firstName = $name[0];
            $lastName = $name[0];
        }
        $data = [
            'email' => $userInfo['email'],
            'firstname' => $firstName,
            'lastname' => $lastName,
            'sendemail' => 0,
            'confirmation' => 0,
            'magenest_sociallogin_id' => $userInfo['id'],
            'magenest_sociallogin_type' => $this->_type
        ];
        return $data;
    }
}
