<?php
/**
 * DAppCrypto
 * Website: dappcrypto.io
 * GitHub Website: dappcrypto.github.io
 * GitHub: https://github.com/dappcrypto
 */

namespace cpay\services;

use InvalidArgumentException;

class sAbi
{
    /* ============================================
     * STATIC COMPAT LAYER
     * ============================================ */
    public static function EncodeGroup(array $types, array $values): string
    {
        $abi = new self();
        return $abi->encodeParameters($types, $values);
    }

    /* ============================================
     * PUBLIC
     * ============================================ */
    public function encodeParameters(array $types, array $values): string
    {
        if (count($types) !== count($values)) {
            throw new InvalidArgumentException('Types/values count mismatch');
        }

        $head = '';
        $tail = '';
        $offset = 32 * count($types);

        foreach ($types as $i => $type) {
            if ($this->isDynamic($type)) {
                $head .= $this->padUint($offset);
                $encoded = $this->encodeType($type, $values[$i]);
                $tail .= $encoded;
                $offset += strlen($encoded) / 2;
            } else {
                $head .= $this->encodeType($type, $values[$i]);
            }
        }

        return $head . $tail;
    }

    /* ============================================
     * TYPE DISPATCHER
     * ============================================ */
    private function encodeType(string $type, mixed $value): string
    {
        return match(true){
        $type === 'uint256'   => $this->padUint($value),
            $type === 'address'   => $this->padAddress($value),
            $type === 'string'    => $this->encodeString($value),
            $type === 'bytes32'   => $this->padBytes32($value),
            $type === 'uint256[]' => $this->encodeUintArray($value),
            $type === 'address[]' => $this->encodeAddressArray($value),
            $type === 'string[]'  => $this->encodeStringArray($value),
            default => throw new InvalidArgumentException("Unsupported ABI type: {$type}")
        };
    }

    private function isDynamic(string $type): bool
    {
        return $type === 'string' || str_ends_with($type, '[]');
    }

    /* ============================================
     * SCALARS
     * ============================================ */
    private function padUint($value): string
    {
        if (is_string($value) && str_starts_with($value, '0x')) {
            $num = gmp_init(substr($value, 2), 16);
        } else {
            $num = gmp_init((string)$value, 10);
        }
        return str_pad(gmp_strval($num, 16), 64, '0', STR_PAD_LEFT);
    }

    private function padAddress(string $address): string
    {
        $addr = strtolower($address);

        if (str_starts_with($addr, '0x')) {
            $addr = substr($addr, 2);
        }

        if (!ctype_xdigit($addr) || strlen($addr) !== 40) {
            throw new InvalidArgumentException('Address must be 20 bytes (40 hex chars)');
        }

        return str_pad($addr, 64, '0', STR_PAD_LEFT);
    }

    private function padBytes32(string $value): string
    {
        $hex = strtolower(ltrim($value, '0x'));
        if (strlen($hex) !== 64) {
            throw new InvalidArgumentException('bytes32 must be exactly 32 bytes');
        }
        return $hex;
    }

    /* ============================================
     * DYNAMIC
     * ============================================ */
    private function encodeString(string $value): string
    {
        $hex = bin2hex($value);
        $len = strlen($hex) / 2;
        return $this->padUint($len) . str_pad($hex, ceil(strlen($hex) / 64) * 64, '0', STR_PAD_RIGHT);
    }

    private function encodeUintArray(array $arr): string
    {
        $out = $this->padUint(count($arr));
        foreach ($arr as $v) {
            $out .= $this->padUint($v);
        }
        return $out;
    }

    private function encodeAddressArray(array $arr): string
    {
        $out = $this->padUint(count($arr));
        foreach ($arr as $v) {
            $out .= $this->padAddress($v);
        }
        return $out;
    }

    private function encodeStringArray(array $arr): string
    {
        $head = $this->padUint(count($arr));
        $tail = '';
        $offset = 32 * count($arr);

        foreach ($arr as $v) {
            $head .= $this->padUint($offset);
            $encoded = $this->encodeString($v);
            $tail .= $encoded;
            $offset += strlen($encoded) / 2;
        }

        return $head . $tail;
    }
}
