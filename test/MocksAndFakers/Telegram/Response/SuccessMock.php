<?php

namespace TelegramApp\Test\MocksAndFakers\Telegram\Response;

use TelegramApp\Test\MocksAndFakers\AbstractResponseMock;

class SuccessMock extends AbstractResponseMock
{
    protected array $responseArray = [
        'ok' => true,
        'result' => [],
    ];
}
