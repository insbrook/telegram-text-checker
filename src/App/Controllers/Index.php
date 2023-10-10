<?php

namespace TelegramApp\App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TelegramApp\Framework\Http\Response\HtmlResponse;

/**
 * Just echo web app html contents
 */
class Index implements ControllerInterface
{
    public function handleRequest(ServerRequestInterface $serverRequest): ResponseInterface
    {
        if (empty($_SERVER['HTTP_ACCEPT'])) {
            // Greetings to console users!
            return (new HtmlResponse())->withData("Welcome to text checking Telegram App!\n");
        }

        /*
         * Feel free to use any template engine here
         */
        return (new HtmlResponse())->withData(
            file_get_contents(ROOT_DIR . '/public/app.htm')
        );
    }
}
