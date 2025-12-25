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

// TODO Create authorization for the user

$postData = $_REQUEST;
if(empty($postData['nOrder'])){
    echo 'nOrder is empty ';
}

$aRecipient='0x...';

// Get Order Data
if(!empty($postData['nOrder'])){
    $nOrder = $postData['nOrder'];

    $OrderData = CPay::instance()->getOrderData($aRecipient, $nOrder);
    if($OrderData['error']!=0){
        // Payment failed or is pending confirmation. Please try again later.
        echo '<pre>';
        print_r($OrderData);
        echo '</pre>';
    } else {
        // TODO The order has been successfully paid.
        /*echo '<pre>';
        print_r($OrderData);
        echo '</pre>';*/

        ?>
        <h1>Payment for the order was successful</h1>
        <ul>
            <li>Recipient: <?= $OrderData['aRecipient'] ?></li>
            <li>Recipient Actual: <?= $OrderData['pDataArr']['sPayData']['aRecipientExt'] ?></li>
            <li>Order ID: <?= $OrderData['nOrder'] ?></li>
            <li>Chain ID: <?= $OrderData['chain'] ?></li>
        </ul>
        <?php
    }
}

