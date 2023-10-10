<?php

namespace TelegramApp\App\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TelegramApp\App\TelegramClient;
use TelegramApp\Framework\Exception\ClientSafeException;
use TelegramApp\Framework\Http\Response\JsonResponse;

class Status implements ControllerInterface
{
    protected TelegramClient $telegramClient;

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function handleRequest(ServerRequestInterface $serverRequest): ResponseInterface
    {
        /** @var TelegramClient $client */
        $this->telegramClient = $this->container->get(TelegramClient::class);
        $me = $this->telegramClient->getMe();
        if (!$me) {
            throw new ClientSafeException("Token is OK, but Telegram response is empty");
        }

        $webhook = $this->telegramClient->getWebhookInfo();
        $saveUpdateId = empty($webhook['url']);
        $lastProcessedUpdateId = null;
        if ($saveUpdateId) {
            $lastProcessedUpdateId = $this->telegramClient->getLastProcessedUpdateId();
            // Check value writing
            $this->telegramClient->setLastProcessedUpdateId($lastProcessedUpdateId);
        }

        $result = [
            'Time' => date(\DATE_ATOM),
            'Token' => 'OK',
            'Username' => $me['username'],
        ];

        if (empty($webhook['url'])) {
            $result['WebHook'] = 'Not set';
            $result['Last processed update id'] = $lastProcessedUpdateId;
        } else {
            $result['WebHook'] = $webhook['url'];
            $result['Not processed update queue'] = $webhook['pending_update_count'] ?? 0;
        }

        $result['Everything is OK'] = 'Yes, sir!';

        return (new JsonResponse)->withData($result);
    }
}
