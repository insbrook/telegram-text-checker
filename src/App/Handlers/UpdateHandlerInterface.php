<?php

namespace TelegramApp\App\Handlers;

use TelegramApp\App\TelegramClient;

interface UpdateHandlerInterface
{
    public function handle(TelegramClient $telegramClient, int $updateId, array $updateInfo): mixed;
}
