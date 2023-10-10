<?php

namespace TelegramApp\Test\App\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use TelegramApp\Test\AppTestCase;
use TelegramApp\Test\MocksAndFakers\Telegram\Response\GetMeMock;
use TelegramApp\Test\MocksAndFakers\Telegram\Response\GetWebHookInfoEmptyUrlMock;

class StatusTest extends AppTestCase
{
    public function testError()
    {
        $result = $this->post('/status');
        $this->assertEquals('Token is OK, but Telegram response is empty', $result);
    }

    public function testOk()
    {
        $mock = new MockHandler([
            new GetMeMock(),
            new GetWebHookInfoEmptyUrlMock(),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $this->container->set(ClientInterface::class . '@telegram', $client);

        $result = $this->post('/status');
        $this->assertIsArray($result);
        unset($result['Time']);
        $expected = [
            'Token' => 'OK',
            'Username' => 'TextGearsBot',
            'WebHook' => 'Not set',
            'Last processed update id' => 0,
            'Everything is OK' => 'Yes, sir!',
        ];
        $this->assertEquals($expected, $result);
    }
}
