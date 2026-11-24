<?php
/**
 * DAppCrypto
 * Website: dappcrypto.org
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

$PrivateKey = ''; // Your private key for aRecipient
$PDataObj = [
        'aRecipient'=>'0x...', // Your wallet account address (aRecipient)
        'aRecipientExt'=>'0x...', // Your wallet address for receiving USDT, USDC cryptocurrency
        'start'=>time(),
        'deadline'=>(time()+(60*60*24*365)), // Deadline time seconds UTC
        'nOrder'=>time(), // Order ID
        'nRToken'=>"1.50", // 1.50 - Amount USDT/USDC
        'nRType'=>'1', // 1 - USDT/USDC
        'nTax2'=>'0', // 100 - 1% Tax partner, 0 - 0% Tax partner
        'aTax2'=>'0x0000000000000000000000000000000000000000', // Partner tax wallet address
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
    <div class="cpayBlock">
        <h1>Example Invoice</h1>
        <ul>
            <li>Recipient: <?= $PDataObj['aRecipient'] ?></li>
            <li>Recipient Actual: <?= $PDataObj['aRecipient'] ?></li>
            <li>Order ID: <?= $PDataObj['nOrder'] ?></li>
            <li>Price: <?= $PDataObj['nRToken'] ?> <?= ($PDataObj['nRType']==1)?'USDT/USDC':'' ?></li>
            <li><a target="_blamk" href="<?= $InvoiceData['LinkInvoice'] ?>">Pay</a></li>
        </ul>
    </div>
    <?php
}



// CSS
?>
    <style>
        .cpayBlock {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            width: 100%;
            max-width: 440px;
            margin: 24px auto;
            background-color: #ffffff;
            color: #1f2937;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            box-sizing: border-box;
            overflow: hidden;
        }

        .cpayBlock h1 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
            padding: 20px 24px 16px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
            background-color: #f9fafb;
        }

        .cpayBlock ul {
            list-style: none;
            margin: 0;
            padding: 8px 24px 24px;
        }

        .cpayBlock ul li {
            padding: 12px 0;
            border-bottom: 1px dashed #e5e7eb;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #4b5563;
            word-break: break-all; /* Перенос длинных крипто-адресов */
        }

        .cpayBlock ul li:nth-last-child(2) {
            font-size: 1.15rem;
            font-weight: 700;
            color: #111827;
            border-bottom: none;
            padding-top: 16px;
            padding-bottom: 8px;
            word-break: normal;
        }

        .cpayBlock ul li:last-child {
            border-bottom: none;
            padding-bottom: 0;
            padding-top: 12px;
        }

        .cpayBlock ul li a {
            display: block;
            width: 100%;
            background-color: #2563eb;
            color: #ffffff;
            text-align: center;
            text-decoration: none;
            font-size: 1.05rem !important;
            font-weight: 600;
            padding: 14px 20px;
            border-radius: 10px;
            box-sizing: border-box;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .cpayBlock ul li a:hover {
            background-color: #1d4ed8;
        }

        .cpayBlock ul li a:active {
            transform: scale(0.98);
        }
    </style>

<?php

