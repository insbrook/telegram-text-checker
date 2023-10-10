<?php

namespace TelegramApp\Framework;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * Basic sample PSR-compatible logger
 */
class FileLogger implements LoggerInterface
{
    use LoggerTrait;

    protected null | string $logPath = null;

    public function __construct(array $config)
    {
        $this->logPath = $config['path'] ?? null;
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        if (!$this->logPath) {
            return;
        }

        @file_put_contents(
            $this->logPath,
            "[{$level}] at " . date(\DATE_ATOM) . "\n{$message}\nContext: " . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n",
            FILE_APPEND
        );
    }
}
