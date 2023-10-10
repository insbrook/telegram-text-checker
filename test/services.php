<?php

namespace TelegramApp\Test;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use TelegramApp\App\TelegramClient;
use TelegramApp\Test\MocksAndFakers;

return [
    // Container testing config
    MocksAndFakers\SampleFactory::class => [],
    'test' => MocksAndFakers\SampleFactory::class,

    // Set separete path for runtime files
    TelegramClient::class => [
        ClientInterface::class . '@telegram',
        [
            'cursorFilePath' => __DIR__ . '/runtime/lastid.txt',
        ]
    ],

    // Set HTTP-clients with mocks
    MocksAndFakers\TextGears\HttpClientMockedFactory::class => [],
    MocksAndFakers\Telegram\HttpClientMockedFactory::class => [],

    ClientInterface::class . '@checker' => MocksAndFakers\TextGears\HttpClientMockedFactory::class,
    ClientInterface::class . '@telegram' => MocksAndFakers\Telegram\HttpClientMockedFactory::class,

    // Log to an array for debug purposes
    LoggerInterface::class => MocksAndFakers\ArrayLogger::class,
    MocksAndFakers\ArrayLogger::class => [],
];
