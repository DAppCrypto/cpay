<?php
/**
 * DAppCrypto
 * Website: dappcrypto.org
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

$aRecipient='0x...'; // Your wallet account address (aRecipient)

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
        <div class="cpayBlock">
            <h1>Payment for the order was successful</h1>
            <div class="cpaySuccessful"></div>
            <ul>
                <li>Recipient: <?= $OrderData['aRecipient'] ?></li>
                <li>Recipient Actual: <?= $OrderData['pDataArr']['sPayData']['aRecipientExt'] ?></li>
                <li>Order ID: <?= $OrderData['nOrder'] ?></li>
                <li>Chain ID: <?= $OrderData['chain'] ?></li>
                <li><a target="_blank" href="<?= $OrderData['PaymentReceiptLink'] ?>">Receipt link</a> </li>
            </ul>
        </div>
        <?php
    }
}


// CSS
?>
    <style>
        /* Основной контейнер карточки */
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

        /* Заголовок */
        .cpayBlock h1 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
            padding: 20px 24px 16px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
            background-color: #f9fafb;
            line-height: 1.3;
        }

        /* --- БЛОК УСПЕШНОЙ ОПЛАТЫ (БОЛЬШАЯ ГАЛОЧКА) --- */
        .cpayBlock .cpaySuccessful {
            width: 76px;
            height: 76px;
            margin: 24px auto 8px;
            background-color: #dcfce7; /* Мягкий зелёный фон круга */
            border-radius: 50%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: cpayPopIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        /* Сама галочка внутри круга */
        .cpayBlock .cpaySuccessful::after {
            content: "";
            width: 20px;
            height: 38px;
            border: solid #16a34a; /* Насыщенный зелёный цвет галочки */
            border-width: 0 5px 5px 0;
            border-radius: 2px;
            transform: rotate(45deg);
            margin-top: -6px; /* Визуальное центрирование */
        }

        @keyframes cpayPopIn {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        /* ---------------------------------------------- */

        /* Список параметров */
        .cpayBlock ul {
            list-style: none;
            margin: 0;
            padding: 8px 24px 24px;
        }

        /* Пункты списка */
        .cpayBlock ul li {
            padding: 12px 0;
            border-bottom: 1px dashed #e5e7eb;
            font-size: 0.9rem;
            line-height: 1.5;
            color: #4b5563;
            word-break: break-all;
        }

        /* Выделение цены ТОЛЬКО на странице оплаты (когда нет .cpaySuccessful) */
        .cpayBlock h1 + ul li:nth-last-child(2) {
            font-size: 1.15rem;
            font-weight: 700;
            color: #111827;
            border-bottom: none;
            padding-top: 16px;
            padding-bottom: 8px;
            word-break: normal;
        }

        /* Убираем нижнюю границу у последнего текстового пункта перед кнопкой на странице успеха */
        .cpayBlock .cpaySuccessful + ul li:nth-last-child(2) {
            border-bottom: none;
            padding-bottom: 8px;
        }

        /* Контейнер для кнопки */
        .cpayBlock ul li:last-child {
            border-bottom: none;
            padding-bottom: 0;
            padding-top: 12px;
        }

        /* Кнопка оплаты (синяя) */
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

        /* Кнопка ссылки на чек на странице успеха (зелёная) */
        .cpayBlock .cpaySuccessful + ul li a {
            background-color: #16a34a;
        }

        .cpayBlock .cpaySuccessful + ul li a:hover {
            background-color: #15803d;
        }

    </style>

<?php

