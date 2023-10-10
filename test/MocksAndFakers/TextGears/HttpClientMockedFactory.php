<?php

namespace TelegramApp\Test\MocksAndFakers\TextGears;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Psr\Container\ContainerInterface;

class HttpClientMockedFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $mock = new MockHandler([
            new GrammarMock(),
        ]);

        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }
}
