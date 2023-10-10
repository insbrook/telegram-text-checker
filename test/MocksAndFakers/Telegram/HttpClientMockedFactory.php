<?php

namespace TelegramApp\Test\MocksAndFakers\Telegram;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Psr\Container\ContainerInterface;
use TelegramApp\Test\MocksAndFakers\Telegram\Response\SuccessMock;

class HttpClientMockedFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $mock = new MockHandler([
            new SuccessMock(),
        ]);

        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }
}
