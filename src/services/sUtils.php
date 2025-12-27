<?php
/**
 * DAppCrypto
 * Website: dappcrypto.io
 * GitHub Website: dappcrypto.github.io
 * GitHub: https://github.com/dappcrypto
 */

namespace cpay\services;

class sUtils
{
    protected static $instance;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public static function strip0x(string $hex): string
    {
        return str_starts_with($hex, '0x') ? substr($hex, 2) : $hex;
    }

    /**
     * Get:
     * - hex (0x… или без)
     * - binary (32 bytes)
     *   Return binary (32 bytes)
     */
    public static function hex2bin32(string $value): string
    {
        // ✅ Уже binary bytes32
        if (strlen($value) === 32) {
            return $value;
        }

        // ✅ Hex
        $hex = self::strip0x($value);

        if (!ctype_xdigit($hex)) {
            throw new \InvalidArgumentException('hex2bin32(): invalid hex input');
        }

        return hex2bin(str_pad($hex, 64, '0', STR_PAD_LEFT));
    }

    public static function bin2hex0x(string $bin): string
    {
        return '0x' . bin2hex($bin);
    }

    public static function keccak(string $bin): string
    {
        return sKeccak256::hash($bin); // binary in → binary out
    }

    function floatToDecimals(float $amount, int $decimals): string
    {
        $str = sprintf('%.' . $decimals . 'f', $amount);
        $str = rtrim(rtrim($str, '0'), '.');
        return bcmul($str, bcpow('10', (string)$decimals), 0);
    }

    function decimalsToFloat($amount, int $decimals): float
    {
        $result = bcdiv((string)$amount, bcpow('10', (string)$decimals), $decimals);
        return (float)$result;
    }

    function validatePData(array $PDataObj): array
    {
        // aRecipient — основной адрес (ETH/BSC)
        if (empty($PDataObj['aRecipient'])) {
            return ['error' => 1, 'data' => 'aRecipient is empty'];
        }
        if (!is_string($PDataObj['aRecipient']) || strlen($PDataObj['aRecipient']) !== 42) {
            return ['error' => 1, 'data' => 'aRecipient address is invalid'];
        }

        // aRecipientExt — адрес для получения USDT / USDC
        if (empty($PDataObj['aRecipientExt'])) {
            return ['error' => 1, 'data' => 'aRecipientExt is empty'];
        }
        if (!is_string($PDataObj['aRecipientExt']) || strlen($PDataObj['aRecipientExt']) !== 42) {
            return ['error' => 1, 'data' => 'aRecipientExt address is invalid'];
        }

        // deadline
        if (empty($PDataObj['deadline'])) {
            return ['error' => 1, 'data' => 'deadline is empty'];
        }
        if (!is_numeric($PDataObj['deadline']) || $PDataObj['deadline'] <= time()) {
            return ['error' => 1, 'data' => 'deadline is invalid'];
        }

        // nOrder
        if (empty($PDataObj['nOrder'])) {
            return ['error' => 1, 'data' => 'nOrder is empty'];
        }
        if (!is_numeric($PDataObj['nOrder'])) {
            return ['error' => 1, 'data' => 'nOrder is invalid'];
        }

        // nRToken — amount
        if (empty($PDataObj['nRToken'])) {
            return ['error' => 1, 'data' => 'nRToken is empty'];
        }
        if (!is_numeric($PDataObj['nRToken']) || $PDataObj['nRToken'] <= 0) {
            return ['error' => 1, 'data' => 'nRToken is invalid'];
        }

        // nRType — token type
        if (empty($PDataObj['nRType'])) {
            return ['error' => 1, 'data' => 'nRType is empty'];
        }
        if (!in_array((string)$PDataObj['nRType'], ['1', '2'], true)) {
            return ['error' => 1, 'data' => 'nRType is invalid'];
        }

        // sTextData
        if (empty($PDataObj['sTextData'])) {
            return ['error' => 1, 'data' => 'sTextData is empty'];
        }
        if (!is_string($PDataObj['sTextData'])) {
            return ['error' => 1, 'data' => 'sTextData is invalid'];
        }

        // start
        if (empty($PDataObj['start'])) {
            return ['error' => 1, 'data' => 'start is empty'];
        }
        if (!is_numeric($PDataObj['start'])) {
            return ['error' => 1, 'data' => 'start is invalid'];
        }

        return ['error' => 0, 'data' => 'ok'];
    }

    public static function pubKeyToAddress(string $pubKey): string
    {
        if (strlen($pubKey) !== 65 || $pubKey[0] !== "\x04") {
            throw new \Exception('Invalid public key');
        }

        $hash = sKeccak256::hash(substr($pubKey, 1));

        return '0x' . substr(bin2hex($hash), 24);
    }

    public static function hex2bin32_2(string $hex): string
    {
        return str_pad(hex2bin($hex), 32, "\x00", STR_PAD_LEFT);
    }
}