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
        // EIP-712 digest (32 bytes)
        $digest = self::digest($hashBin);
        // binary signature (65 bytes r||s||v)
        $sigBin = sSecp256k1::sign($digest, $privateKey);
        // ETH format: 0x + hex
        return '0x' . bin2hex($sigBin);
    }

    /**
     * ethers.verifyTypedData(...)
     *
     * @param string $msgHashHex  bytes32 (hex without 0x)
     * @param string $signature  65 bytes (r+s+v)
     * @return string            Ethereum address (0x...)
     */
    public static function signer(string $msgHashHex, string $signatureHex): string
    {
        $digest = self::digest($msgHashHex);
        $signatureBin = hex2bin(substr($signatureHex, 2));
        $pubKey = sSecp256k1::recover($digest, $signatureBin);
        return sUtils::pubKeyToAddress($pubKey);
    }
}