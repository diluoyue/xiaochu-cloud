<?php

/**
 * Created by PhpStorm.
 * User: xudong.ding
 * Date: 16/5/19
 * Time: 下午2:09
 */
class AlipayF2FPrecreateResult
{
    private $tradeStatus;
    private $response;

    public function AlipayF2FPrecreateResult($response)
    {
        $this->__construct($response);
    }

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function getTradeStatus()
    {
        return $this->tradeStatus;
    }

    public function setTradeStatus($tradeStatus)
    {
        $this->tradeStatus = $tradeStatus;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }
}

?>