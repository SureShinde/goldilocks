<?php
namespace Magenest\SocialLogin\Controller\Linkedin;

use Magenest\SocialLogin\Controller\AbstractConnect;

/**
 * Class Connect
 * @package Magenest\SocialLogin\Controller\Linkedin
 */
class Connect extends AbstractConnect
{
    /**
     * @var string
     */
    protected $_exeptionMessage = 'Linked in login failed.';

    /**
     * @var string
     */
    protected $_type = 'linkedin';

    /**
     * @var string
     */
    protected $_path = '/userinfo';

    /**
     * @var string
     */
    protected $clientModel = '\Magenest\SocialLogin\Model\Linkedin\Client';


    /**
     * @param $client
     * @param $code
     */
    public function getUserInfo($client, $code)
    {
        $userInfo = $client->api('/me', $code);
        $userInfo['name'] = $userInfo['localizedFirstName'].' '.$userInfo['localizedLastName'];
        return $userInfo;
    }

    /**
     * @param $userInfo
     * @return array
     */
    public function getDataNeedSave($userInfo)
    {
        $dataParent = parent::getDataNeedSave($userInfo);

        $data = [
            'email' => $userInfo['email'],
            'firstname' => $userInfo['localizedFirstName'],
            'lastname' => $userInfo['localizedLastName'],
            'sendemail' => 0,
            'confirmation' => 0,
            'magenest_sociallogin_id' => $userInfo['id'],
            'magenest_sociallogin_type' => $this->_type
        ];

        return array_replace_recursive($dataParent, $data);
    }
}
