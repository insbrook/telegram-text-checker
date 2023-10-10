<?php

namespace TelegramApp\App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ControllerInterface
{
    public function handleRequest(ServerRequestInterface $serverRequest): ResponseInterface;
}
