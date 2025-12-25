<?php
/**
 * DAppCrypto
 * Website: dappcrypto.io
 * GitHub Website: dappcrypto.github.io
 * GitHub: https://github.com/dappcrypto
 */

namespace cpay\services;

class sKeccak256
{
    /**
     * Ethereum sKeccak256
     * @param string $input binary string
     * @return string binary hash (32 bytes)
     */
    public static function hash(string $input): string
    {
        // ВАЖНО:
        // mdlen = 256
        // raw_output = true →
        return sKeccak::hash($input, 256, true);
    }
}