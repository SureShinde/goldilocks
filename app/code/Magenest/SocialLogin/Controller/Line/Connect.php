<?php
namespace Magenest\SocialLogin\Controller\Line;

use Magenest\SocialLogin\Controller\AbstractConnect;

/**
 * Class Connect
 * @package Magenest\SocialLogin\Controller\Line
 */
class Connect extends AbstractConnect
{
    /**
     * @var string
     */
    protected $_exeptionMessage = 'Line login failed.';

    /**
     * @var string
     */
    protected $_type = 'line';

    /**
     * @var string
     */
    protected $_path = '/v2/profile';

    /**
     * @var string
     */
    protected $clientModel = '\Magenest\SocialLogin\Model\Line\Client';

    /**
     * @param $userInfo
     * @return array
     */
    public function getDataNeedSave($userInfo)
    {
        $dataParent = parent::getDataNeedSave($userInfo);
        $userName = $userInfo['displayName'];
        $data = [
            'email' => $userInfo['email'],
            'firstname' => $userName,
            'lastname' => $userName,
        ];

        return array_replace_recursive($dataParent, $data);
    }

    /**
     * @param $client
     * @param $code
     */
    public function getUserInfo($client, $code)
    {
        $userInfo = $client->api($this->_path, $code);

        if (isset($userInfo['userId'])) {
            $userInfo['id'] = $userInfo['userId'];
        }

        if (isset($userInfo['displayName'])) {
            $userInfo['name'] = $userInfo['displayName'];
        }
        return $userInfo;
    }
}
