<?php

namespace TelegramApp\Test\MocksAndFakers;

use stdClass;

class SampleFactory
{
    public function __invoke(): stdClass
    {
        return new stdClass();
    }
}
