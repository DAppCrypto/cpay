<?php
/**
 * DAppCrypto
 * Website: dappcrypto.io
 * GitHub Website: dappcrypto.github.io
 * GitHub: https://github.com/dappcrypto
 */

namespace cpay\services;

class sEIP712
{
    private const DOMAIN_TYPE =
        "EIP712Domain(string name,string version)";

    private const DATA_TYPE =
        "Data(bytes32 hash)";

    /* ---------- helpers ---------- */

    private static function domainSeparator(): string
    {
        return sKeccak256::hash(
            sKeccak256::hash(self::DOMAIN_TYPE) .
            sKeccak256::hash("domainDAPP") .
            sKeccak256::hash("1")
        );
    }

    private static function structHash(string $msgHashHex): string
    {
        return sKeccak256::hash(
            sKeccak256::hash(self::DATA_TYPE) .
            sUtils::hex2bin32($msgHashHex)
        );
    }

    private static function digest(string $msgHashHex): string
    {
        return sKeccak256::hash(
            "\x19\x01" .
            self::domainSeparator() .
            self::structHash($msgHashHex)
        );
    }

    /* ---------- API ---------- */

    public static function sign(string $privateKey, string $hashBin): string
    {
        return sSecp256k1::sign(
            self::digest($hashBin),
            $privateKey
        );
    }

    public static function signer(string $hashBin, string $signature): string
    {
        return sSecp256k1::recover(
            self::digest($hashBin),
            $signature
        );
    }
}