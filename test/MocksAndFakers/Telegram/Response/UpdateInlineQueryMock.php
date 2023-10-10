<?php

namespace TelegramApp\Test\MocksAndFakers\Telegram\Response;

use TelegramApp\Test\MocksAndFakers\AbstractResponseMock;

class UpdateInlineQueryMock extends AbstractResponseMock
{
    protected array $responseArray = [
        'ok' => true,
        'result' => [
            [
                'update_id' => 111333,
                'inline_query' => [
                    'id' => '2220000000000000',
                    'from' =>
                        [
                            'id' => 111,
                            'is_bot' => false,
                            'first_name' => 'Alex',
                            'last_name' => 'Y',
                            'username' => 'user_login_here',
                            'language_code' => 'en',
                            'is_premium' => true,
                        ],
                    'chat_type' => 'private',
                    'query' => 'I goes to school ever day. My favorite lessons is math and drawing.',
                    'offset' => '',
                ],
            ],
        ],
    ];
}