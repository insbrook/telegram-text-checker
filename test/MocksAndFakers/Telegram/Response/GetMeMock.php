<?php

namespace TelegramApp\Test\MocksAndFakers\Telegram\Response;

use TelegramApp\Test\MocksAndFakers\AbstractResponseMock;

class GetMeMock extends AbstractResponseMock
{
    protected array $responseArray = [
        'ok' => true,
        'result' => [
            'id' => 5141827376,
            'is_bot' => true,
            'first_name' => 'TextGears text checker',
            'username' => 'TextGearsBot',
            'can_join_groups' => true,
            'can_read_all_group_messages' => false,
            'supports_inline_queries' => true,
        ],
    ];
}