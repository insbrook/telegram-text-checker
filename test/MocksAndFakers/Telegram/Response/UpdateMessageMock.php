<?php

namespace TelegramApp\Test\MocksAndFakers\Telegram\Response;

use TelegramApp\Test\MocksAndFakers\AbstractResponseMock;

class UpdateMessageMock extends AbstractResponseMock
{
    /**
     * @var array
     */
    protected array $responseArray = [
        'ok' => true,
        'result' => [
            [
                'update_id' => 11111,
                'message' => [
                    'message_id' => 111,
                    'from' =>
                        [
                            'id' => 222,
                            'is_bot' => false,
                            'first_name' => 'Alex',
                            'last_name' => 'Y',
                            'username' => 'some_user_name',
                            'language_code' => 'en',
                            'is_premium' => true,
                        ],
                    'chat' =>
                        [
                            'id' => 222,
                            'first_name' => 'Alex',
                            'last_name' => 'Y',
                            'username' => 'some_user_name',
                            'type' => 'private',
                        ],
                    'date' => 1696694998,
                    'text' => 'I goes to school ever day. My favorite lessons is math and drawing.',
                ],
            ]
        ],
    ];
}