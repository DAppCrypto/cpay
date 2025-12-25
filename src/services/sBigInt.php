<?php
/**
 * DAppCrypto
 * Website: dappcrypto.io
 * GitHub Website: dappcrypto.github.io
 * GitHub: https://github.com/dappcrypto
 */

namespace cpay\services;

class sBigInt
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

    public static function init(string $n, int $base = 10)
    {
        $n = trim($n);
        if ($n === "") $n = "0";
        if ($base === 16 && str_starts_with($n, "0x")) {
            $n = substr($n, 2);
        }

        return gmp_init($n, $base);
    }

    public static function add($a, $b) { return gmp_add($a, $b); }
    public static function sub($a, $b) { return gmp_sub($a, $b); }
    public static function mul($a, $b) { return gmp_mul($a, $b); }
    public static function mod($a, $b) { return gmp_mod($a, $b); }
    public static function cmp($a, $b) { return gmp_cmp($a, $b); }
    public static function div_q($a, $b) { return gmp_div_q($a, $b); }

    public static function invert($a, $mod)
    {
        $inv = gmp_invert($a, $mod);
        if ($inv === false) {
            throw new \Exception('Inverse does not exist');
        }
        return $inv;
    }

    public static function powmod($a, $e, $m)
    {
        return gmp_powm($a, $e, $m);
    }

    public static function testBit($a, int $bit): bool
    {
        return gmp_testbit($a, $bit);
    }

    public static function str($a, int $base = 10): string
    {
        return gmp_strval($a, $base);
    }

    public static function neg($a)
    {
        return gmp_neg($a);
    }

    public static function randomRange($min, $max)
    {
        $range = gmp_sub($max, $min);
        if (gmp_cmp($range, 0) <= 0) return $min;

        $bytesLength = intdiv(gmp_strlen($range, 2) + 7, 8);
        do {
            $bytes = random_bytes($bytesLength);
            $k = gmp_init(bin2hex($bytes), 16);
            $k = gmp_mod($k, gmp_add($range, 1));
        } while (gmp_cmp($k, $min) < 0 || gmp_cmp($k, $max) > 0);

        return gmp_add($min, $k);
    }
}