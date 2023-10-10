<?php

namespace TelegramApp\Test;

use PHPUnit\Framework\TestCase;
use TelegramApp\Framework;
use TelegramApp\App\Controllers;

class AppTestCase extends TestCase
{
    protected Framework\App | null $app = null;
    protected Framework\Container | null $container = null;

    /**
     * Set up Container and create App instance for testing purposes
     *
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function setUp(): void
    {
        parent::setUp();

        $_REQUEST = $_GET = $_POST = [];

        $_ENV = array_merge($_ENV, parse_ini_file(__DIR__ . '/.env') ?: []);

        if (!defined('ROOT_DIR')) {
            define('ROOT_DIR', dirname(__DIR__));
        }
        @unlink(__DIR__ . '/runtime/lastid.txt');

        $this->container = new Framework\Container(array_merge(
            require __DIR__ . '/../src/services.php',
            require __DIR__ . '/services.php',
        ));

        $this->app = $this->container->get(Framework\App::class);
    }

    protected function post(string $uri, array $data = [], ?int $assertCode = null)
    {
        $_REQUEST = $_POST = $data;
        $_SERVER['REQUEST_URI'] = $uri;
        $_SERVER['HTTP_ACCEPT'] = 'text/html;application/json';

        $response = $this->app->run($this->container, [
            '/poll' => Controllers\Poll::class,
            '/grammar' => Controllers\CheckGrammar::class,
            '/status' => Controllers\Status::class,
            '/' => Controllers\Index::class,
        ]);

        $this->assertEquals($assertCode ?: 200, $response->getStatusCode());
        $contentType = $response->getHeaders()['Content-Type'][0] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $resultArray = json_decode((string)$response->getBody(), true);
            return $resultArray;
        }

        return (string)$response->getBody();
    }
}
