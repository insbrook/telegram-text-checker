<?php

namespace TelegramApp\Test\MocksAndFakers\Telegram\Response;

use TelegramApp\Test\MocksAndFakers\AbstractResponseMock;

class UpdateCallbackQueryMock extends AbstractResponseMock
{
    protected array $responseArray = [
        'ok' => true,
        'result' => [
            [
                'update_id' => 111222,
                'callback_query' => [
                    'id' => '111000000000000',
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
                    'message' => [
                        'message_id' => 181,
                        'from' => [
                            'id' => 5141827376,
                            'is_bot' => true,
                            'first_name' => 'TextGears text checker',
                            'username' => 'TextGearsBot',
                        ],
                        'chat' => [
                            'id' => 777888,
                            'first_name' => 'Alex',
                            'last_name' => 'Y',
                            'username' => 'user_login_here',
                            'type' => 'private',
                        ],
                        'date' => 1696694801,
                        'text' => 'I goes[go] to school ever day[every day]. My favorite lessons is[are] math and drawing.',
                        'entities' => [
                            0 => [
                                'type' => 'strikethrough',
                                'offset' => 2,
                                'length' => 4,
                            ],
                            1 => [
                                'type' => 'bold',
                                'offset' => 6,
                                'length' => 4,
                            ],
                            2 => [
                                'type' => 'strikethrough',
                                'offset' => 21,
                                'length' => 8,
                            ],
                            3 => [
                                'type' => 'bold',
                                'offset' => 29,
                                'length' => 11,
                            ],
                            4 => [
                                'type' => 'strikethrough',
                                'offset' => 62,
                                'length' => 2,
                            ],
                            5 => [
                                'type' => 'bold',
                                'offset' => 64,
                                'length' => 5,
                            ],
                        ],
                        'reply_markup' => [
                            'inline_keyboard' => [
                                0 => [
                                    0 => [
                                        'text' => 'Show corrected ☑️',
                                        'callback_data' => 'correct',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'chat_instance' => '-123000000000000000',
                    'data' => 'correct',
                ],
            ],
        ],
    ];
}
