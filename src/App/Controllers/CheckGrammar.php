<?php

namespace TelegramApp\App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TelegramApp\App\TextChecker\TextCheckingProvider;
use TelegramApp\Framework\Exception\ClientSafeException;
use TelegramApp\Framework\Http\Response\JsonResponse;
use TelegramApp\Framework\ResponseProcessor;

class CheckGrammar implements ControllerInterface
{
    public function __construct(
        protected TextCheckingProvider $textCheckingProvider
    ) {
        //
    }

    public function handleRequest(ServerRequestInterface $serverRequest): ResponseInterface
    {
        $text = $serverRequest->getParsedBody()['text'] ?? $_REQUEST['text'] ?? '';

        return (new JsonResponse)->withData([
            'status' => true,
            'response' => [
                'errors' => $this->textCheckingProvider->getErrors($text),
            ],
        ]);
    }
}
