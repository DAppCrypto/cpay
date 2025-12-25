<?php
/**
 * DAppCrypto
 * Website: dappcrypto.io
 * GitHub Website: dappcrypto.github.io
 * GitHub: https://github.com/dappcrypto
 */

namespace cpay\services;

class sException
{
    private string $message;
    private int $code;
    private string $file;
    private int $line;

    public function __construct(string $message, int $code = 0)
    {
        $this->message = $message;
        $this->code = $code;

        // Capture where the object was created
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $this->file = $trace['file'] ?? 'unknown';
        $this->line = $trace['line'] ?? 0;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function __toString(): string
    {
        return sprintf(
            "Exception: %s (Code %d) in %s on line %d",
            $this->message,
            $this->code,
            $this->file,
            $this->line
        );
    }
}