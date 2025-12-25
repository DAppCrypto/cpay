<?php

namespace cpay;

require_once (__DIR__ . '/../autoload.php');

use cpay\services\sPay;

class CPay
{

    protected static $instance;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    protected $data;

    public function __construct($data=[])
    {
        $this->data = $data;
    }

    public function createInvoice(array $PDataObj, string $PrivateKey, $DefaultChainId=1): array
    {
        return sPay::instance()->createInvoice($PDataObj, $PrivateKey, $DefaultChainId);
    }

    public function getWebhook($aRecipient): array
    {
        return sPay::instance()->getWebhook($aRecipient);
    }

    public function getOrderData($aRecipient, $nOrder): array
    {
        return sPay::instance()->getOrderData($aRecipient, $nOrder);
    }

    public function getAddressByPrivateKey(string $PrivateKey): array
    {
       return sPay::instance()->getAddressByPrivateKey($PrivateKey);
    }

    public function getTextData(array $ArrData): array
    {
        return sPay::instance()->getTextData($ArrData);
    }

}
