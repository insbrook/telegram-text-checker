<?php

namespace TelegramApp\Framework;

use Psr\Http\Message\ResponseInterface;

class ResponseProcessor
{
    public function sendResponse(ResponseInterface $response = null)
    {
        if (!$response) {
            return;
        }

        if (!empty($_SERVER['HTTP_ACCEPT'])) {
            http_response_code($response->getStatusCode() ?: 200);
            foreach ($response->getHeaders() as $header => $values) {
                foreach ($values as $value) {
                    header("{$header}: $value");
                }
            }
        }
        echo $response->getBody();
    }
}
