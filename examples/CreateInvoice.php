<?php
/**
 * DAppCrypto
 * Website: dappcrypto.io
 * GitHub Website: dappcrypto.github.io
 * GitHub: https://github.com/dappcrypto
 */

// Create a shop in dappcrypto.github.io

/*
## ⚠ IMPORTANT SECURITY WARNINGS

Attention!
A **separate account with a private key** must be used ONLY for:
    - shop registration
- shop management on dappcrypto.github.io
- invoice creation

Attention!
To receive cryptocurrency payments**, specify a different wallet in the SDK
and keep its private key strictly secret.

Failure to follow these rules may result in loss of funds.

*/

require_once (__DIR__ . '/../autoload.php');
//require_once (__DIR__.'/../src/CPay.php');

use cpay\CPay;

$PrivateKey = '...'; // Your private key for aRecipient
$PDataObj = [
    'aRecipient'=>'0x...', // Your wallet account address (aRecipient)
    'aRecipientExt'=>'0x...', // Your wallet address for receiving USDT, USDC cryptocurrency
    'deadline'=>(time()+(60*60*24*365)), // Deadline time seconds UTC
    'nOrder'=>time(), // Order ID
    'nRToken'=>"0.01", // Amount USDT, USDC
    'nRType'=>'1', // 1 - USDT, 2 - USDC
    'sTextData'=>CPay::instance()->getTextData(['success'=>'','error'=>'','return'=>'','Description'=>''])['base64String'],
    'start'=>time(),
];
$DefaultChainId = 1;

$InvoiceData = CPay::instance()->createInvoice($PDataObj, $PrivateKey, $DefaultChainId);
if($InvoiceData['error']!=0){
    // Error creating invoice
    echo '<pre>';
    print_r($InvoiceData);
    echo '</pre>';
} else {
    // The invoice has been successfully created.
    ?>
    <h1>Example Invoice</h1>
    <ul>
        <li>Recipient: <?= $PDataObj['aRecipient'] ?></li>
        <li>Recipient Actual: <?= $PDataObj['aRecipient'] ?></li>
        <li>Order ID: <?= $PDataObj['nOrder'] ?></li>
        <li>Price: <?= $PDataObj['nRToken'] ?> <?= ($PDataObj['nRType']==1)?'USDT':'USDC' ?></li>
        <li><a style="font-size: 2em;" target="_blamk" href="<?= $InvoiceData['LinkInvoice'] ?>">Pay</a></li>
    </ul>
    <?php
}