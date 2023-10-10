<?php

namespace TelegramApp\App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TelegramApp\App\Handlers;
use TelegramApp\Framework\Http\Response\HtmlResponse;

class Poll extends Status implements ControllerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected int $lifeTimeSeconds = -1;

    public function handleRequest(ServerRequestInterface $serverRequest): ResponseInterface
    {
        // Before polling we need to perform some necessary checks.
        // We just call Status controller which creates TelegramClient
        // and throws an exception on problems.
        parent::handleRequest($serverRequest);

        $start = time();
        if (intval($_ENV['POLLING_LIFETIME'] ?? 0)) {
            $this->lifeTimeSeconds = intval($_ENV['POLLING_LIFETIME'] ?? 0);
        }

        $this->printInfo("Polling started at " . date(\DATE_ATOM) . "\n");

        $lastId = $this->telegramClient->getLastProcessedUpdateId();
        while ($this->lifeTimeSeconds < 1 || time() < $start + $this->lifeTimeSeconds) {
            $updates = $this->telegramClient->getUpdates($lastId);
            foreach ($updates as $update) {
                if (empty($update['update_id'])) {
                    continue;
                }
                $lastId = $update['update_id'];

                /*
                 * Let's find a Handler and run it.
                 * Next we must setLastProcessedUpdateId even in case of Exception!
                 */
                try {
                    $handlerMap = [
                        'inline_query' => Handlers\InlineQueryHandler::class,
                        'message' => Handlers\MessageHandler::class,
                        'callback_query' => Handlers\CallbackQueryHandler::class,
                    ];
                    foreach ($handlerMap as $type => $handlerClass) {
                        if (empty($update[$type])) {
                            continue;
                        }
                        $handler = $this->container->get($handlerClass);
                        $handler->handle($this->telegramClient, $lastId, $update);
                    }
                } catch (\Exception $ex) {
                    // Check the output for possible errors
                    if ($this->logger) {
                        $this->logger->error("Polling error: {$ex->getMessage()}");
                    }
                    $this->printInfo($ex->getMessage() . "\n");
                } finally {
                    // Save update id in any case
                    $this->telegramClient->setLastProcessedUpdateId($lastId);
                    $this->printInfo('+');
                }
            }

            if (!empty($_ENV['POLLING_ONCE'])) {
                break;
            }
        }

        // Empty response
        return (new HtmlResponse())->withData("Polling succeeded at " . date(\DATE_ATOM) . "\n");
    }

    protected function printInfo(string $text)
    {
        if (!empty($_ENV['POLLING_SILENT'])) {
            return;
        }
        echo $text;
    }
}
