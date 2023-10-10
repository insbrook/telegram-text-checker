<?php

namespace TelegramApp\Test\MocksAndFakers;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;

abstract class AbstractResponseMock extends Response
{
    protected array $responseArray = [];

    public function __construct(int $status = 200, array $headers = [], $body = null, string $version = '1.1', string $reason = null)
    {
        if (!$body) {
            $body = Utils::streamFor(json_encode($this->responseArray));
        }
        parent::__construct($status, $headers, $body, $version, $reason);
    }
}
