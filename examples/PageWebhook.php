<?php
/**
 * DAppCrypto
 * Website: dappcrypto.io
 * GitHub Website: dappcrypto.github.io
 * GitHub: https://github.com/dappcrypto
 */

require_once (__DIR__ . '/../autoload.php');
//require_once (__DIR__.'/../src/CPay.php');

use cpay\CPay;

$aRecipient='0x...';
$WebhookData = CPay::instance()->getWebhook($aRecipient);
if($WebhookData['error']!=0){
    // Order error
    print_r($WebhookData);
} else {
    // TODO Order success
    echo 'Order: '.$WebhookData['orderData']['nOrder'].' was successfully paid in chain id: '.$WebhookData['orderData']['chain'].' aRecipient:'. $WebhookData['orderData']['aRecipient'];
}

