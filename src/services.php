<?php

namespace TelegramApp;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use LanguageDetection;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use TelegramApp\App\Controllers;
use TelegramApp\App\Handlers;
use TelegramApp\App\I18n;
use TelegramApp\App\TelegramClient;
use TelegramApp\App\TextChecker;
use TelegramApp\Framework;

/**
 * Simple dependency injection config.
 * For testing purposes each element can be replaced with a mock or faker if needed.
 */

return [
    /*
     * Basics
     */
    ContainerInterface::class => Framework\Container::class,
    Framework\App::class => [],
    Framework\ResponseProcessor::class => [],

    /*
     * Controllers and services
     */
    TelegramClient::class => [
        ClientInterface::class . '@telegram',
        [
            'cursorFilePath' => ROOT_DIR . '/data/lastid.txt',
        ]
    ],
    Controllers\Poll::class => [
        ContainerInterface::class,
    ],
    Controllers\Status::class => [
        ContainerInterface::class,
    ],
    Controllers\Index::class => [],
    Controllers\CheckGrammar::class => [
        TextChecker\TextCheckingProvider::class,
    ],

    Handlers\InlineQueryHandler::class => [
        TextChecker\TextCheckingProvider::class,
        I18n\Translator::class,
    ],
    Handlers\MessageHandler::class => [
        TextChecker\TextCheckingProvider::class,
        I18n\Translator::class,
    ],
    Handlers\CallbackQueryHandler::class => [
        TextChecker\TextCheckingProvider::class,
        I18n\Translator::class,
    ],

    I18n\Translator::class => [],

    /*
     * Text checking routines
     */
    TextChecker\TextGearsChecker::class => [
        ClientInterface::class . '@checker',
    ],
    TextChecker\DummyChecker::class => [],
    TextChecker\TextCheckingProvider::class => [
        LanguageDetection\Language::class,
        ContainerInterface::class,
        // An array of available checkers. You can add or replace with your own
        [
            TextChecker\TextGearsChecker::class,
            TextChecker\DummyChecker::class,
        ],
        [
            // Allow-list of supported languages
            'en-US', 'en-GB', 'en-ZA', 'en-AU', 'en-NZ', 'fr-FR', 'de-DE', 'de-AT', 'de-CH',
            'pt-PT', 'pt-BR', 'it-IT', 'ar-AR', 'ru-RU', 'es-ES', 'ja-JP', 'zh-CN', 'el-GR'
        ]
    ],
    LanguageDetection\Language::class => [],

    /*
     * Third-parties
     */
    // You can have two different clients for two API providers with separate settings
    ClientInterface::class . '@telegram' => Client::class,
    ClientInterface::class . '@checker' => Client::class,
    Client::class => [],
    LoggerInterface::class => Framework\FileLogger::class,
    Framework\FileLogger::class => [
        [
            'path' => __DIR__ . '/../data/log.txt',
        ]
    ],
];
