<?php

use TelegramApp\Framework;
use TelegramApp\App\Controllers;

define('ROOT_DIR',  dirname(__DIR__));
define('ENV_PATH',  ROOT_DIR . '/.env');

// Enabled for debug purposes, remove for production
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once ROOT_DIR . '/vendor/autoload.php';

$_ENV = array_merge($_ENV, @parse_ini_file(ENV_PATH) ?: []);

$container = new Framework\Container(require ROOT_DIR . '/src/services.php');

/** @var Framework\App $app */
$app = $container->get(Framework\App::class);

$response = $app->run($container, [
    '/poll' => Controllers\Poll::class,
    '/grammar' => Controllers\CheckGrammar::class,
    '/status' => Controllers\Status::class,
    '/' => Controllers\Index::class,
]);

$container->get(Framework\ResponseProcessor::class)->sendResponse($response);
