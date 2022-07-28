<?php
/**
 * Ipay88 Inc
 * @author Ipay88 Inc <pvchi@ipay88.com>
 * @package Ipay88\Lib
 */

class Ipay88_Signature
{
    protected $source;

    protected $signature;

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
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



    public function __construct($source = null)
    {
        if($source) {
            $this->source = $source;
            $this->setSignature($this->generateSignature());
        }
    }

    public function generateSignature($source = null) {
        $source = $source ? : $this->getSource();

        return base64_encode($this->hex2bin(sha1($source)));
    }

    protected function hex2bin($hexSource)
    {
        $bin = '';
        for ($i = 0; $i < strlen($hexSource); $i = $i + 2)
        {
            $bin .= chr(hexdec(substr($hexSource, $i, 2)));
        }
        return $bin;
    }
}