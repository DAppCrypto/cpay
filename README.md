# DApp Crypto Payment Gateway SDK

PHP SDK for accepting cryptocurrency payments **without KYC/KYB**.  
Supports MetaMask, Trust Wallet and popular cryptocurrencies.

---

## 🚀 Features

- No KYC / No KYB
- Crypto payments
- Wallets: MetaMask, Trust Wallet
- Currencies: USDT, USDC, ETH, BTC
- Networks: Ethereum, BNB Smart Chain and others
- Invoice generation
- Self-custody
- Composer installation
- MIT License

---

## 🔧 Installation

```bash
composer require dappcrypto/cpay
```

### Create Invoice

[examples/CreateInvoice.php](/cpay/examples/CreateInvoice.php) - Example Create Invoice

```bash
use cpay\CPay;

$PrivateKey = '...'; // Your private key for aRecipient
$PDataObj = [
    'aRecipient'=>'0x...', // Your wallet account address (aRecipient)
    'aRecipientExt'=>'0x...', // Your wallet address for receiving USDT, USDC cryptocurrency
    'deadline'=>(time()+(60*60*24*365)), // Deadline time seconds UTC
    'nOrder'=>'1', // Order ID
    'nRToken'=>"1.50", // Amount USDT, USDC
    'nRType'=>'1', // 1 - USDT, 2 - USDC
    'sTextData'=>CPay::instance()->getTextData(['success'=>'','error'=>'','return'=>'','Description'=>''])['base64String'],
    'start'=>time(),
];
$DefaultChainId = 1;

$InvoiceData = CPay::instance()->createInvoice($PDataObj, $PrivateKey, $DefaultChainId);
if($InvoiceData['error']!=0){
    // Error creating invoice
} else {
    // The invoice has been successfully created.
    echo '<a href="'.$InvoiceData['LinkInvoice'].'">Pay</a>';
}
```

### Get Invoice Status

```bash
use cpay\CPay;

$aRecipient = '0x...';
$nOrder = '1';

$OrderData = CPay::instance()->getOrderData($aRecipient, $nOrder);
if($OrderData['error']!=0){
    // Payment failed or is pending confirmation. Please try again later.
} else {
    // TODO The order has been successfully paid.
}
```

### Webhook

[examples/PageWebhook.php](/cpay/examples/PageWebhook.php) - Example Create Invoice

```bash
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
```

---

## Shop Registration (No KYC / No KYB)

### Registration steps:

1. **Create a new account in MetaMask wallet**
2. Go to **https://dappcrypto.github.io** and create a shop in Ethereum, BNB Smart Chain and others  
3. Set the URL
    - [examples/PageWebhook.php](/cpay/examples/PageWebhook.php) - Example Webhook page
    - [examples/PageSuccess.php](/cpay/examples/PageSuccess.php) - Example Success page
4. Use the **private key and wallet address** of this account in the SDK

---

## ⚠ IMPORTANT SECURITY WARNINGS

**Attention!**  
A **separate account with a private key** must be used **ONLY** for:
- shop registration
- shop management on **dappcrypto.github.io**
- invoice creation

**Attention!**  
To **receive cryptocurrency payments**, specify a **different wallet** in the SDK  
and **keep its private key strictly secret**.

Failure to follow these rules may result in **loss of funds**.

---

## Requirements

- PHP **>= 8.0**

---

## Contacts

- Website: https://dappcrypto.io
- GitHub Website: https://dappcrypto.github.io
- Telegram: https://t.me/DAppCryptos



