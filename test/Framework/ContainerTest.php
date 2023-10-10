<?php

namespace TelegramApp\Test\Framework;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use TelegramApp\Framework\App;
use TelegramApp\Test\AppTestCase;

class ContainerTest extends AppTestCase
{
    public function testGet()
    {
        $app = $this->container->get(App::class);
        $this->assertInstanceOf(App::class, $app);
    }

    public function testGetByAlias()
    {
        $client = $this->container->get(ClientInterface::class . '@telegram');
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testFactory()
    {
        $obj1 = $this->container->get('test');
        $this->assertInstanceOf(\stdClass::class, $obj1);
    }
}
