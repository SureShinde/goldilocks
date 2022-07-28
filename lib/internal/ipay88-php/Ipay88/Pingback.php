<?php
/**
 * Created by PhpStorm.
 * User: shinichi
 * Date: 6/15/16
 * Time: 17:58
 */
class Ipay88_Pingback {
    /**
     * @var
     */
    protected $merchantCode;

    /**
     * @var
     */
    protected $refNo;


    /**
     * @var
     */
    protected $paymentId;

    /**
     * @var
     */
    protected $encoding;

    /**
     * @var
     */
    protected $status;

    /**
     * @var
     */
    protected $signature;


    /**
     * @var string
     */
    protected $amount;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var string
     */
    protected $productDesc;

    /**
     * @var string
     */
    protected $userName;

    /**
     * @var string
     */
    protected $userContact;

    /**
     * @var string
     */
    protected $userEmail;

    /**
     * @var string
     */
    protected $remark;

    /**
     * @var string
     */
    protected $backendUrl;

    /**
     * @var string
     */
    protected $responseUrl;

    public function __construct($options)
    {
        if(isset($options['MerchantCode'])) {
            $this->merchantCode = $options['MerchantCode'];
        }
        if(isset($options['PaymentId'])) {
            $this->paymentId = $options['PaymentId'];
        }
        if(isset($options['RefNo'])) {
            $this->refNo = $options['RefNo'];
        }
        if(isset($options['Amount'])) {
            $this->amount = $options['Amount'];
        }
        if(isset($options['Currency'])) {
            $this->currency = $options['Currency'];
        }
        if(isset($options['ProdDesc'])) {
            $this->productDesc = $options['ProdDesc'];
        }
        if(isset($options['UserName'])) {
            $this->userName = $options['UserName'];
        }
        if(isset($options['UserEmail'])) {
            $this->userEmail = $options['UserEmail'];
        }
        if(isset($options['UserContact'])) {
            $this->userContact = $options['UserContact'];
        }
        if(isset($options['Remark'])) {
            $this->remark = $options['Remark'];
        }
        if(isset($options['Status'])) {
            $this->status = $options['Status'];
        }
        if(isset($options['Signature'])) {
            $this->signature = $options['Signature'];
        }
        if(isset($options['ResponseURL'])) {
            $this->responseUrl = $options['ResponseURL'];
        }
        if(isset($options['BackendURL'])) {
            $this->backendUrl = $options['BackendURL'];
        }
    }

    /**
     * @return mixed
     */
    public function getMerchantCode()
    {
        return $this->merchantCode;
    }

    /**
     * @param mixed $merchantCode
     */
    public function setMerchantCode($merchantCode)
    {
        $this->merchantCode = $merchantCode;
    }

    /**
     * @return mixed
     */
    public function getRefNo()
    {
        return $this->refNo;
    }

    /**
     * @param mixed $refNo
     */
    public function setRefNo($refNo)
    {
        $this->refNo = $refNo;
    }

    /**
     * @return mixed
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @param mixed $paymentId
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
    }

    /**
     * @return mixed
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param mixed $encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param mixed $signature
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getProductDesc()
    {
        return $this->productDesc;
    }

    /**
     * @param string $productDesc
     */
    public function setProductDesc($productDesc)
    {
        $this->productDesc = $productDesc;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getUserContact()
    {
        return $this->userContact;
    }

    /**
     * @param string $userContact
     */
    public function setUserContact($userContact)
    {
        $this->userContact = $userContact;
    }

    /**
     * @return string
     */
    public function getUserEmail()
    {
        return $this->userEmail;
    }

    /**
     * @param string $userEmail
     */
    public function setUserEmail($userEmail)
    {
        $this->userEmail = $userEmail;
    }

    /**
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }

    /**
     * @return string
     */
    public function getBackendUrl()
    {
        return $this->backendUrl;
    }

    /**
     * @param string $backendUrl
     */
    public function setBackendUrl($backendUrl)
    {
        $this->backendUrl = $backendUrl;
    }

    /**
     * @return string
     */
    public function getResponseUrl()
    {
        return $this->responseUrl;
    }

    /**
     * @param string $responseUrl
     */
    public function setResponseUrl($responseUrl)
    {
        $this->responseUrl = $responseUrl;
    }


}