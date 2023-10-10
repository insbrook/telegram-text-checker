<?php

namespace TelegramApp\Test\MocksAndFakers\Telegram\Response;

use TelegramApp\Test\MocksAndFakers\AbstractResponseMock;

class GetWebHookInfoEmptyUrlMock extends AbstractResponseMock
{
    protected array $responseArray = [
        'ok' => true,
        'result' => [
            'url' => '',
            'has_custom_certificate' => false,
            'pending_update_count' => 0,
        ],
    ];
}
