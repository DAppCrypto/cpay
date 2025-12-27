<?php
/**
 * DAppCrypto
 * Website: dappcrypto.io
 * GitHub Website: dappcrypto.github.io
 * GitHub: https://github.com/dappcrypto
 */

namespace cpay\services;

class sSecp256k1
{
    private const P  = '0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F';
    private const N  = '0xFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141';
    private const GX = '55066263022277343669578718895168534326250603453777594175500187360389116729240';
    private const GY = '32670510020758816978083085130507043184471273380659243275938904335757337482424';

    /**
     * Ethereum address from private key
     *
     * @param string $privateKey 64 hex chars (no 0x)
     * @return string 0x-prefixed Ethereum address
     */
    public static function addressFromPrivateKey(string $privateKey): string
    {
        if (str_starts_with($privateKey, '0x')) {
            throw new \Exception('Private key must be without 0x');
        }

        if (strlen($privateKey) !== 64 || !ctype_xdigit($privateKey)) {
            throw new \Exception('Private key must be 64 hex characters');
        }

        $d = gmp_init($privateKey, 16);

        // Q = d * G
        $Q = self::ecMul(
            $d,
            [gmp_init(self::GX), gmp_init(self::GY)]
        );

        // uncompressed public key
        $pubKey =
            "\x04" .
            str_pad(gmp_export($Q[0]), 32, "\x00", STR_PAD_LEFT) .
            str_pad(gmp_export($Q[1]), 32, "\x00", STR_PAD_LEFT);

        // Ethereum address = keccak256(pubkey[1:])[12:]
        $hash = sKeccak256::hash(substr($pubKey, 1));

        return '0x' . substr(bin2hex($hash), 24);
    }

    /**
     * Ethereum-compatible ecrecover
     *
     * @param string $hash      32 bytes (binary)
     * @param string $signature 65 bytes (binary r||s||v)
     * @return string           uncompressed public key (65 bytes)
     */
    public static function recover(string $hash, string $signature): string
    {
        if (strlen($signature) !== 65) {
            throw new \Exception('Invalid signature length');
        }

        // --- parse signature ---
        $r = gmp_init(bin2hex(substr($signature, 0, 32)), 16);
        $s = gmp_init(bin2hex(substr($signature, 32, 32)), 16);
        $v = ord($signature[64]);

        if ($v !== 27 && $v !== 28) {
            throw new \Exception('Invalid v value');
        }

        // Ethereum parity: 0 or 1
        $parity = $v - 27;

        $p = gmp_init(self::P);
        $n = gmp_init(self::N);

        // --- ETH RULE: x = r (NO r+n, NO recId) ---
        $x = $r;

        if (gmp_cmp($x, $p) >= 0) {
            throw new \Exception('Invalid r value');
        }

        // --- recover R.y from curve equation ---
        // y² = x³ + 7 mod p
        $alpha = gmp_mod(
            gmp_add(
                gmp_powm($x, 3, $p),
                7
            ),
            $p
        );

        // sqrt modulo p (p % 4 == 3)
        $beta = gmp_powm(
            $alpha,
            gmp_div_q(gmp_add($p, 1), 4),
            $p
        );

        // choose correct y by parity
        $isOdd = gmp_intval(gmp_mod($beta, 2));
        $y = ($isOdd === $parity) ? $beta : gmp_sub($p, $beta);

        $R = [$x, $y];

        // --- Q = r⁻¹ (sR − eG) ---
        $e  = gmp_init(bin2hex($hash), 16);
        $rInv = gmp_invert($r, $n);

        $sr = self::ecMul($s, $R);
        $eg = self::ecMul($e, [gmp_init(self::GX), gmp_init(self::GY)]);

        $Q = self::ecMul(
            $rInv,
            self::ecSub($sr, $eg)
        );

        return self::encodePubKey($Q);
    }

    /* ---------- helpers ---------- */

    private static function encodePubKey(array $Q): string
    {
        return
            "\x04" .
            str_pad(gmp_export($Q[0]), 32, "\x00", STR_PAD_LEFT) .
            str_pad(gmp_export($Q[1]), 32, "\x00", STR_PAD_LEFT);
    }

    /* ---------- elliptic curve math ---------- */

    private static function ecAdd($P, $Q)
    {
        if ($P === null) return $Q;
        if ($Q === null) return $P;

        [$x1, $y1] = $P;
        [$x2, $y2] = $Q;

        $p = gmp_init(self::P);

        if (gmp_cmp($x1, $x2) === 0) {
            if (gmp_cmp($y1, $y2) !== 0) {
                return null;
            }
            return self::ecDouble($P);
        }

        $m = gmp_mod(
            gmp_mul(
                gmp_sub($y2, $y1),
                gmp_invert(gmp_sub($x2, $x1), $p)
            ),
            $p
        );

        $x3 = gmp_mod(gmp_sub(gmp_sub(gmp_pow($m, 2), $x1), $x2), $p);
        $y3 = gmp_mod(gmp_sub(gmp_mul($m, gmp_sub($x1, $x3)), $y1), $p);

        return [$x3, $y3];
    }

    private static function ecDouble($P)
    {
        [$x, $y] = $P;
        $p = gmp_init(self::P);

        $m = gmp_mod(
            gmp_mul(
                gmp_mul(3, gmp_pow($x, 2)),
                gmp_invert(gmp_mul(2, $y), $p)
            ),
            $p
        );

        $x3 = gmp_mod(gmp_sub(gmp_pow($m, 2), gmp_mul(2, $x)), $p);
        $y3 = gmp_mod(gmp_sub(gmp_mul($m, gmp_sub($x, $x3)), $y), $p);

        return [$x3, $y3];
    }

    private static function ecSub($P, $Q)
    {
        return self::ecAdd($P, [$Q[0], gmp_neg($Q[1])]);
    }

    private static function ecMul($k, $P)
    {
        $result = null;
        $addend = $P;

        while (gmp_cmp($k, 0) > 0) {
            if (gmp_testbit($k, 0)) {
                $result = self::ecAdd($result, $addend);
            }
            $addend = self::ecDouble($addend);
            $k = gmp_div_q($k, 2);
        }

        return $result;
    }


    /**
     * Ethereum-compatible ECDSA sign
     *
     * @param string $hash 32 bytes (binary)
     * @param string $privateKey hex (with or without 0x)
     * @return string 65 bytes binary (r||s||v)
     */
    public static function sign(string $hash, string $privateKey): string
    {
        if (str_starts_with($privateKey, '0x')) {
            $privateKey = substr($privateKey, 2);
        }

        $d = gmp_init($privateKey, 16);
        $n = gmp_init(self::N);
        $p = gmp_init(self::P);

        $e = gmp_init(bin2hex($hash), 16);

        // --- deterministic k (RFC6979) ---
        $k = self::rfc6979($hash, $d);

        // R = kG
        $R = self::ecMul($k, [gmp_init(self::GX), gmp_init(self::GY)]);
        $r = gmp_mod($R[0], $n);

        if (gmp_cmp($r, 0) === 0) {
            throw new \Exception('Invalid r');
        }

        $kInv = gmp_invert($k, $n);
        $s = gmp_mod(gmp_mul($kInv, gmp_add($e, gmp_mul($r, $d))), $n);

        if (gmp_cmp($s, 0) === 0) {
            throw new \Exception('Invalid s');
        }

        // --- EIP-2: enforce low-s ---
        $halfN = gmp_div_q($n, 2);
        $vParity = gmp_intval(gmp_mod($R[1], 2));

        if (gmp_cmp($s, $halfN) > 0) {
            $s = gmp_sub($n, $s);
            $vParity ^= 1;
        }

        // Ethereum v = 27 or 28
        $v = 27 + $vParity;

        return
            str_pad(gmp_export($r), 32, "\x00", STR_PAD_LEFT) .
            str_pad(gmp_export($s), 32, "\x00", STR_PAD_LEFT) .
            chr($v);
    }

    /* ---------- RFC6979 ---------- */

    private static function rfc6979(string $hash, $x)
    {
        $xBin = str_pad(gmp_export($x), 32, "\x00", STR_PAD_LEFT);

        $v = str_repeat("\x01", 32);
        $k = str_repeat("\x00", 32);

        $k = hash_hmac('sha256', $v . "\x00" . $xBin . $hash, $k, true);
        $v = hash_hmac('sha256', $v, $k, true);
        $k = hash_hmac('sha256', $v . "\x01" . $xBin . $hash, $k, true);
        $v = hash_hmac('sha256', $v, $k, true);

        do {
            $v = hash_hmac('sha256', $v, $k, true);
            $candidate = gmp_init(bin2hex($v), 16);
        } while (
            gmp_cmp($candidate, 1) < 0 ||
            gmp_cmp($candidate, gmp_init(self::N)) >= 0
        );

        return $candidate;
    }
}