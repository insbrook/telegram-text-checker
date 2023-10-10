<?php

namespace TelegramApp\Test\MocksAndFakers;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * Logger for test purposes.
 * On service config FileLogger is replaced with this one.
 */
class ArrayLogger implements LoggerInterface
{
    use LoggerTrait;

    protected array $array = [];

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->array[] = [
            'level' => $level,
            'message' => (string)$message,
            'context' => $context,
        ];
    }

    public function getLogs(): array
    {
        return $this->array;
    }
}
