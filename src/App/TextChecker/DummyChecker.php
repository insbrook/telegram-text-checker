<?php

namespace TelegramApp\App\TextChecker;

/**
 * Dummy text checker. Used for testing purposes
 * or as a fallback text checking routine.
 */
class DummyChecker implements TextCheckerInterface
{
    public function getErrors(string $text, string $language): array
    {
        return [];
    }

    public function isAvailable(): bool
    {
        return true;
    }
}
