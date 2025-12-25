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
    private const P  = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F';
    private const N  = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141';
    private const GX = '79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798';
    private const GY = '483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8';

    /* ---------- PUBLIC API ---------- */
    public static function sign(string $hash, string $privateKey): string
    {
        $z = sBigInt::init(bin2hex($hash), 16);
        $d = sBigInt::init(ltrim($privateKey, '0x'), 16);
        $n = sBigInt::init(self::N, 16);

        do {
            $k = self::deterministicK($hash, $privateKey);
            [$x, $y] = self::mulPoint($k);

            $r = sBigInt::mod($x, $n);
            if (sBigInt::cmp($r, 0) === 0) continue;

            $kInv = sBigInt::invert($k, $n);
            $s = sBigInt::mod(
                sBigInt::mul($kInv, sBigInt::add($z, sBigInt::mul($r, $d))),
                $n
            );

            if (sBigInt::cmp($s, 0) === 0) continue;

            // recovery id как у kornrunner
            $recid = sBigInt::testBit($y, 0) ? 1 : 0;
            if (sBigInt::cmp($x, $r) !== 0) {
                $recid |= 2;
            }

            // low-s (EIP-2) + ОБЯЗАТЕЛЬНЫЙ XOR
            if (sBigInt::cmp($s, sBigInt::div_q($n, 2)) > 0) {
                $s = sBigInt::sub($n, $s);
                $recid ^= 1;
            }

        } while (false);

        // Solidity ожидает 27 / 28
        $v = $recid + 27;

        return '0x'
            . str_pad(sBigInt::str($r, 16), 64, '0', STR_PAD_LEFT)
            . str_pad(sBigInt::str($s, 16), 64, '0', STR_PAD_LEFT)
            . str_pad(dechex($v), 2, '0', STR_PAD_LEFT);
    }

    public static function recover(string $hash, string $signature): string
    {
        $sig = ltrim($signature, '0x');
        $r = sBigInt::init(substr($sig, 0, 64), 16);
        $s = sBigInt::init(substr($sig, 64, 64), 16);
        $v = hexdec(substr($sig, 128, 2));
        if ($v >= 27) $v -= 27;

        $z = sBigInt::init(bin2hex($hash), 16);
        $n = sBigInt::init(self::N, 16);
        $p = sBigInt::init(self::P, 16);

        // Восстановление точки R
        $R = self::pointFromX($r, $v);

        $rInv = sBigInt::invert($r, $n);
        $u1 = sBigInt::mod(sBigInt::mul(sBigInt::sub(0, $z), $rInv), $n);
        $u2 = sBigInt::mod(sBigInt::mul($s, $rInv), $n);

        $P1 = self::mulPoint($u1);
        $P2 = self::mulPoint($u2, $R);

        $Q = self::add($P1, $P2);

        $pub = str_pad(sBigInt::str($Q[0], 16), 64, '0', STR_PAD_LEFT)
            . str_pad(sBigInt::str($Q[1], 16), 64, '0', STR_PAD_LEFT);

        return '0x' . substr(bin2hex(sKeccak256::hash(hex2bin($pub))), 24);
    }

    public static function addressByPrivateKey(string $privateKey): string
    {
        $d = sBigInt::init(ltrim($privateKey, '0x'), 16);
        [$x, $y] = self::mulPoint($d);

        $pub = hex2bin(
            str_pad(sBigInt::str($x, 16), 64, '0', STR_PAD_LEFT) .
            str_pad(sBigInt::str($y, 16), 64, '0', STR_PAD_LEFT)
        );

        $hash = sKeccak256::hash($pub);

        return '0x' . substr(bin2hex($hash), 24);
    }

    /* ---------- EC MATH ---------- */
    private static function mulPoint($k, ?array $base = null): array
    {
        $P = $base ?? [sBigInt::init(self::GX,16), sBigInt::init(self::GY,16)];
        $R = null;

        while (sBigInt::cmp($k, 0) > 0) {
            if (sBigInt::testBit($k, 0)) $R = self::add($R, $P);
            $P = self::add($P, $P);
            $k = sBigInt::div_q($k, 2);
        }

        return $R;
    }

    private static function add(?array $P, ?array $Q): ?array
    {
        if ($P === null) return $Q;
        if ($Q === null) return $P;

        [$x1,$y1] = $P;
        [$x2,$y2] = $Q;
        $p = sBigInt::init(self::P,16);

        if (sBigInt::cmp($x1,$x2)===0) {
            if (sBigInt::cmp($y1,$y2)!==0) return null;
            $m = sBigInt::mul(
                sBigInt::mul(3, sBigInt::mul($x1,$x1)),
                sBigInt::invert(sBigInt::mul(2,$y1), $p)
            );
        } else {
            $m = sBigInt::mul(sBigInt::sub($y2,$y1), sBigInt::invert(sBigInt::sub($x2,$x1), $p));
        }
        $m = sBigInt::mod($m,$p);

        $x3 = sBigInt::mod(sBigInt::sub(sBigInt::sub(sBigInt::mul($m,$m),$x1),$x2), $p);
        $y3 = sBigInt::mod(sBigInt::sub(sBigInt::mul($m, sBigInt::sub($x1,$x3)),$y1), $p);

        return [$x3,$y3];
    }

    private static function pointFromX($x, int $odd): array
    {
        $p = sBigInt::init(self::P,16);
        $y2 = sBigInt::mod(sBigInt::add(sBigInt::mul(sBigInt::mul($x,$x),$x),7), $p);
        $y = sBigInt::powmod($y2, sBigInt::div_q(sBigInt::add($p,1),4), $p);

        if (sBigInt::testBit($y,0)!==(bool)$odd) $y = sBigInt::sub($p,$y);
        return [$x,$y];
    }

    private static function deterministicK(string $hash, string $privateKey)
    {
        $x = hex2bin(str_pad(ltrim($privateKey,'0x'),64,'0',STR_PAD_LEFT));
        $h = $hash;

        $v = str_repeat("\x01",32);
        $k = str_repeat("\x00",32);

        $k = hash_hmac('sha256', $v."\x00".$x.$h, $k, true);
        $v = hash_hmac('sha256', $v, $k, true);
        $k = hash_hmac('sha256', $v."\x01".$x.$h, $k, true);
        $v = hash_hmac('sha256', $v, $k, true);

        while(true) {
            $v = hash_hmac('sha256', $v, $k, true);
            $candidate = sBigInt::init(bin2hex($v),16);

            if (sBigInt::cmp($candidate,0)>0 && sBigInt::cmp($candidate,sBigInt::init(self::N,16))<0) {
                return $candidate;
            }

            $k = hash_hmac('sha256', $v."\x00", $k, true);
            $v = hash_hmac('sha256', $v, $k, true);
        }
    }
}