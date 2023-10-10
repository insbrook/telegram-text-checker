<?php

namespace TelegramApp\Test\App\Controllers;

use TelegramApp\Test\AppTestCase;

class IndexTest extends AppTestCase
{
    public function test()
    {
        $result = $this->post('/');
        $this->assertStringStartsWith('<!doctype html>', $result);
    }
}
