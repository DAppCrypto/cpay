<?php
/**
 * DAppCrypto
 * Website: dappcrypto.io
 * GitHub Website: dappcrypto.github.io
 * GitHub: https://github.com/dappcrypto
 */

namespace cpay\services;

class sCoder
{
    protected static $instance;

    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public static function base64ToObj($base64String, $forUrl = false) {
        try {
            if ($forUrl) {
                $base64String = str_replace(['-', '_'], ['+', '/'], $base64String);

                $padding = 4 - (strlen($base64String) % 4);
                if ($padding < 4) {
                    $base64String .= str_repeat('=', $padding);
                }
            }

            $decodedString = base64_decode($base64String, true);
            if ($decodedString === false) {
                throw new \Exception('Invalid base64 string');
            }

            $obj = json_decode($decodedString, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON decode error: ' . json_last_error_msg());
            }

            return ['error' => 0, 'data' => 'Success', 'obj' => $obj];

        } catch (Exception $e) {
            return ['error' => 1, 'data' => 'Error', 'err' => $e->getMessage(), 'obj' => []];
        }
    }

    public static function arrToBase64($obj) {
        try {
            if (!is_array($obj) && !is_object($obj)) {
                return ['error' => 1, 'data' => 'Input must be an array or object'];
            }

            $jsonString = json_encode($obj);
            if ($jsonString === false) {
                throw new \Exception('JSON encode error');
            }

            $base64String = base64_encode($jsonString);

            return ['error' => 0, 'data' => 'Success', 'base64String' => $base64String];

        } catch (Exception $e) {
            return ['error' => 1, 'data' => 'Error encoding', 'err' => $e->getMessage()];
        }
    }

    public static function encodeObj($obj) {
        try {
            // JSON encode
            $json = json_encode($obj);

            if ($json === false) {
                throw new \Exception('JSON encode error: ' . json_last_error_msg());
            }

            // gzip compress
            $compressed = gzencode($json);

            if ($compressed === false) {
                throw new \Exception('Gzip compression failed');
            }

            // Base64 encode
            $b64 = base64_encode($compressed);

            // Base64URL transform
            $b64url = str_replace(['+', '/', '='], ['-', '_', ''], $b64);

            return [
                'error' => 0,
                'data' => 'Success',
                'b64url' => $b64url
            ];

        } catch (\Exception $e) {
            return [
                'error' => 1,
                'data' => 'Error',
                'err' => $e->getMessage()
            ];
        }
    }

    public static function decodeObj($b64url) {
        try {
            // Restore Base64 padding
            $b64 = str_replace(['-', '_'], ['+', '/'], $b64url);
            $padding = 4 - (strlen($b64) % 4);
            if ($padding < 4) {
                $b64 .= str_repeat('=', $padding);
            }

            // Base64 decode
            $compressed = base64_decode($b64, true);
            if ($compressed === false) {
                throw new \Exception('Base64 decode failed');
            }

            // gunzip
            $json = gzdecode($compressed);
            if ($json === false) {
                throw new \Exception('Gzip decompression failed');
            }

            // JSON decode
            $obj = json_decode($json, true);
            if ($obj === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON decode error: ' . json_last_error_msg());
            }

            return [
                'error' => 0,
                'data' => 'Success',
                'obj' => $obj
            ];

        } catch (\Exception $e) {
            return [
                'error' => 1,
                'data' => 'Error',
                'err' => $e->getMessage(),
                'obj' => new stdClass()
            ];
        }
    }
}