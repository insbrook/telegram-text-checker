<?php

namespace TelegramApp\Test\App\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Psr\Log\LoggerInterface;
use TelegramApp\Test\AppTestCase;
use TelegramApp\Test\MocksAndFakers\Telegram\Response\GetMeMock;
use TelegramApp\Test\MocksAndFakers\Telegram\Response\GetWebHookInfoEmptyUrlMock;
use TelegramApp\Test\MocksAndFakers\Telegram\Response\SuccessMock;
use TelegramApp\Test\MocksAndFakers\Telegram\Response\UpdateCallbackQueryMock;
use TelegramApp\Test\MocksAndFakers\Telegram\Response\UpdateInlineQueryMock;
use TelegramApp\Test\MocksAndFakers\Telegram\Response\UpdateMessageMock;

class PollTest extends AppTestCase
{
    public function testIncomingMessage()
    {
        $mock = new MockHandler([
            new GetMeMock(), // getMe
            new GetWebHookInfoEmptyUrlMock(), // getWebHookInfo
            new UpdateMessageMock(), // getUpdates
            new SuccessMock(), // sendMessage
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->container->set(ClientInterface::class . '@telegram', $client);

        $_ENV['POLLING_ONCE'] = true;
        $_ENV['POLLING_SILENT'] = true;
        $result = $this->post('/poll');
        $this->assertStringStartsWith('Polling succeeded at ', $result);

        $requests = $this->container->get(LoggerInterface::class)->getLogs();

        $expected = [
            0 => [
                'level' => 'debug',
                'message' => 'Telegram API call: getMe',
                'context' => [
                    'data' => null,
                ],
            ],
            1 => [
                'level' => 'debug',
                'message' => 'Telegram API call: getWebhookInfo',
                'context' => [
                    'data' => null,
                ],
            ],
            2 => [
                'level' => 'debug',
                'message' => 'Telegram API call: getUpdates',
                'context' => [
                    'data' => [
                        'offset' => 1,
                        'limit' => 10,
                        'timeout' => 60,
                    ],
                ],
            ],
            3 => [
                'level' => 'debug',
                'message' => 'Telegram API call: sendMessage',
                'context' => [
                    'data' => [
                        'chat_id' => 222,
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
                                        'callback_data' => 'correct',
                                        'text' => 'Show corrected ☑️',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $requests);
    }

    public function testInlineQuery()
    {
        $mock = new MockHandler([
            new GetMeMock(), // getMe
            new GetWebHookInfoEmptyUrlMock(), // getWebHookInfo
            new UpdateInlineQueryMock(), // getUpdates
            new SuccessMock(), // answerInlineQuery
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->container->set(ClientInterface::class . '@telegram', $client);

        $_ENV['POLLING_ONCE'] = true;
        $_ENV['POLLING_SILENT'] = true;
        $result = $this->post('/poll');
        $this->assertStringStartsWith('Polling succeeded at ', $result);

        $requests = $this->container->get(LoggerInterface::class)->getLogs();

        $expected = [
            0 => [
                'level' => 'debug',
                'message' => 'Telegram API call: getMe',
                'context' => [
                    'data' => null,
                ],
            ],
            1 => [
                'level' => 'debug',
                'message' => 'Telegram API call: getWebhookInfo',
                'context' => [
                    'data' => null,
                ],
            ],
            2 => [
                'level' => 'debug',
                'message' => 'Telegram API call: getUpdates',
                'context' => [
                    'data' => [
                        'offset' => 1,
                        'limit' => 10,
                        'timeout' => 60,
                    ],
                ],
            ],
            3 => [
                'level' => 'debug',
                'message' => 'Telegram API call: answerInlineQuery',
                'context' => [
                    'data' => [
                        'inline_query_id' => 2220000000000000,
                        'results' => [
                            [
                                'type' => 'article',
                                'id' => 2220000000000000,
                                'title' => 'Send corrected',
                                'description' => 'Found some errors',
                                'input_message_content' => [
                                    'message_text' => 'I go to school every day. My favorite lessons are math and drawing.',
                                ],
                                'hide_url' => true,
                            ]
                        ],
                    ]
                ],
            ],
        ];

        $this->assertEquals($expected, $requests);
    }

    public function testCallbackQuery()
    {
        $mock = new MockHandler([
            new GetMeMock(), // getMe
            new GetWebHookInfoEmptyUrlMock(), // getWebHookInfo
            new UpdateCallbackQueryMock(), // getUpdates
            new SuccessMock(), // sendMessage
            new SuccessMock(), // answerCallbackQuery
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->container->set(ClientInterface::class . '@telegram', $client);

        $_ENV['POLLING_ONCE'] = true;
        $_ENV['POLLING_SILENT'] = true;

        $result = $this->post('/poll');
        $this->assertStringStartsWith('Polling succeeded at ', $result);

        $requests = $this->container->get(LoggerInterface::class)->getLogs();

        $expected = [
            0 => [
                'level' => 'debug',
                'message' => 'Telegram API call: getMe',
                'context' => [
                    'data' => null,
                ],
            ],
            1 => [
                'level' => 'debug',
                'message' => 'Telegram API call: getWebhookInfo',
                'context' => [
                    'data' => null,
                ],
            ],
            2 => [
                'level' => 'debug',
                'message' => 'Telegram API call: getUpdates',
                'context' => [
                    'data' => [
                        'offset' => 1,
                        'limit' => 10,
                        'timeout' => 60,
                    ],
                ],
            ],
            3 => [
                'level' => 'debug',
                'message' => 'Telegram API call: sendMessage',
                'context' => [
                    'data' => [
                        'chat_id' => 777888,
                        'text' => 'I go to school every day. My favorite lessons are math and drawing.',
                    ],
                ],
            ],
            4 => [
                'level' => 'debug',
                'message' => 'Telegram API call: answerCallbackQuery',
                'context' => [
                    'data' => [
                        'callback_query_id' => 111000000000000,
                        'text' => 'OK',
                        'show_alert' => false,
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $requests);
    }
}
