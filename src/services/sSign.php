<?php
/**
 * DAppCrypto
 * Website: dappcrypto.io
 * GitHub Website: dappcrypto.github.io
 * GitHub: https://github.com/dappcrypto
 */

namespace cpay\services;


class sSign
{
    protected static $instance;
    public $PortfolioList;

    public static function instance()
    {

        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    function getAbiCoderData(array $arrTypes, array $arrValues): string
    {
        $abi = new sAbi();

        // encode → binary
        $encoded = $abi->encodeParameters($arrTypes, $arrValues);

        // ethers hex 0x
        return '0x' . $encoded;
    }

    function getHex(string $str): string
    {
        return $hex = bin2hex($str);
    }

    function hexup(string $value): string
    {
        return strlen($value) % 2 === 0 ? $value : "0{$value}";
    }

    function getHashData(string $sData): string
    {
        // sData = "0x...." ABI encoded
        $hex = sUtils::strip0x($sData);
        // hex → binary
        $bin = hex2bin($hex);
        // keccak(binary) → binary (32 bytes)
        return sKeccak256::hash($bin);
    }


    function getSignature(string $message, string $privateKey): string
    {
        return sEIP712::sign($privateKey, $message);
    }


    function getSignerData(string $message, string $signature): string
    {
        return sEIP712::verify($message, $signature);
    }

    function getAddressByPrivateKey(string $privateKey): string
    {
        return sSecp256k1::addressFromPrivateKey($privateKey);
    }

}