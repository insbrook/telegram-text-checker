<?php

namespace TelegramApp\Test\App\Controllers;

use TelegramApp\Test\AppTestCase;

class CheckGrammarTest extends AppTestCase
{
    public function test()
    {
        $result = $this->post('/grammar', [
            'text' => 'I goes to school ever day. My favorite lessons is math and drawing.',
        ]);
        $this->assertNotEmpty($result);
        $this->assertTrue($result['status']);
        $this->assertEquals(3, count($result['response']['errors']));
    }
}
