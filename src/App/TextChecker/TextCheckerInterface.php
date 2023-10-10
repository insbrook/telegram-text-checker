<?php

namespace TelegramApp\App\TextChecker;

interface TextCheckerInterface
{
    /**
     * @param string $text
     * @param string $language
     * @return TextError[]
     */
    public function getErrors(string $text, string $language): array;

    /**
     * @return bool
     */
    public function isAvailable(): bool;
}
