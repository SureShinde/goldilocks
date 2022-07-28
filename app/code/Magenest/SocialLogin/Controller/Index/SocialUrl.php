<?php
namespace Magenest\SocialLogin\Controller\Index;

use Magenest\SocialLogin\Model\Apple\Client as ClientApple;
use Magenest\SocialLogin\Model\Zalo\Client as ClientZalo;
use Magenest\SocialLogin\Model\Pinterest\Client as ClientPinterest;
use Magenest\SocialLogin\Model\Reddit\Client as ClientReddit;
use Magenest\SocialLogin\Model\Amazon\Client as ClientAmazon;
use Magenest\SocialLogin\Model\Facebook\Client as ClientFacebook;
use Magenest\SocialLogin\Model\Google\Client as ClientGoogle;
use Magenest\SocialLogin\Model\Twitter\Client as ClientTwitter;
use Magenest\SocialLogin\Model\Line\Client as ClientLine;
use Magenest\SocialLogin\Model\Linkedin\Client as ClientLinkedin;

use Magento\Framework\App\Action\Context;

class SocialUrl extends \Magento\Framework\App\Action\Action
{
    /* @var ClientApple  */
    protected $clientApple;
    /* @var ClientZalo  */
    protected $clientZalo;
    /* @var ClientPinterest  */
    protected $clientPinterest;
    /* @var ClientReddit  */
    protected $clientReddit;
    /* @var ClientAmazon  */
    protected $clientAmazon;
    /** @var ClientFacebook  */
    protected $clientFacebook;
    /** @var ClientGoogle  */
    protected $clientGoogle;
    /** @var ClientLine  */
    protected $clientLine;
    /** @var ClientLinkedin  */
    protected $clientLinkedin;
    /** @var ClientTwitter  */
    protected $clientTwitter;

    /**
     * @param ClientApple $clientApple
     * @param ClientZalo $clientZalo
     * @param ClientPinterest $clientPinterest
     * @param ClientReddit $clientReddit
     * @param ClientAmazon $clientAmazon
     * @param ClientFacebook $clientFacebook
     * @param ClientTwitter $clientTwitter
     * @param ClientLine $clientLine
     * @param ClientLinkedin $clientLinkedin
     * @param ClientGoogle $clientGoogle
     * @param Context $context
     */
    public function __construct(
        ClientApple $clientApple,
        ClientZalo $clientZalo,
        ClientPinterest $clientPinterest,
        ClientReddit $clientReddit,
        ClientAmazon $clientAmazon,
        ClientFacebook $clientFacebook,
        ClientGoogle $clientGoogle,
        ClientLine $clientLine,
        ClientLinkedin $clientLinkedin,
        ClientTwitter $clientTwitter,
        Context $context
    ) {
        parent::__construct($context);
        $this->clientApple = $clientApple;
        $this->clientZalo = $clientZalo;
        $this->clientPinterest = $clientPinterest;
        $this->clientReddit = $clientReddit;
        $this->clientAmazon = $clientAmazon;
        $this->clientGoogle = $clientGoogle;
        $this->clientFacebook = $clientFacebook;
        $this->clientLine = $clientLine;
        $this->clientLinkedin = $clientLinkedin;
        $this->clientTwitter = $clientTwitter;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest()->getParam('social');
        $response = $this->getResponse();
        switch ($request){
            case "apple":
                $response->setRedirect($this->clientApple->createAuthUrl());
                break;
            case "zalo":
                $response->setRedirect($this->clientZalo->createAuthUrl());
                break;
            case "pinterest":
                $response->setRedirect($this->clientPinterest->createAuthUrl());
                break;
            case "reddit":
                $response->setRedirect($this->clientReddit->createAuthUrl());
                break;
            case "amazon":
                $response->setRedirect($this->clientAmazon->createAuthUrl());
                break;
            case "facebook":
                $response->setRedirect($this->clientFacebook->createAuthUrl());
                break;
            case "google":
                $response->setRedirect($this->clientGoogle->createAuthUrl());
                break;
            case "line":
                $response->setRedirect($this->clientLine->createAuthUrl());
                break;
            case "linkedin":
                $response->setRedirect($this->clientLinkedin->createAuthUrl());
                break;
            case "twitter":
                $response->setRedirect($this->clientTwitter->createAuthUrl());
                break;
            default:
                break;
        }

        return $this->getResponse();
    }
}
