<?php

namespace TelegramApp\App;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TelegramApp\Framework\Exception\ClientSafeException;

class TelegramClient implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected string $cursorFileName = '';

    public function __construct(
        protected ClientInterface $httpClient,
        protected array $options = []
    ) {
        if (empty($_ENV['TELEGRAM_TOKEN'])) {
            if (!file_exists(ENV_PATH)) {
                throw new ClientSafeException("Telegram bot token not set. Env file not exists");
            }
            if (!is_readable(ENV_PATH)) {
                $user = $_SERVER['USERNAME'] ?? $_SERVER['USER'] ?? '';
                throw new ClientSafeException("Telegram bot token not set. Env file exists, but user '{$user}' have no reading access to the file");
            }
            throw new ClientSafeException("Telegram bot token not set. Check your .env file");
        }

        $this->cursorFileName = $this->options['cursorFilePath'] ?? '';
        if (!$this->cursorFileName) {
            $this->cursorFileName = ROOT_DIR . '/data/lastid.txt';
        }
    }

    /**
     * Gets common bot info like id and username.
     * Can be used to check for token validity.
     *
     * @return array|null
     */
    public function getMe(): ?array
    {
        $response = $this->postJson("getMe");
        if (!$response) {
            return null;
        }
        return $response['result'];
    }

    /**
     * Get active webhook info: url and pending queue size
     *
     * @see https://core.telegram.org/bots/api#getwebhookinfo
     * @return array|null
     */
    public function getWebhookInfo(): ?array
    {
        $response = $this->postJson("getWebhookInfo");
        if (!$response) {
            return null;
        }
        return $response['result'];
    }

    /**
     * Get a list of the latest bot events
     *
     * @see https://core.telegram.org/bots/api#getupdates
     * @param int $lastId
     * @return array|null
     */
    public function getUpdates(int $lastId): ?array
    {
        $response = $this->postJson(
            "getUpdates",
            [
                'offset' => $lastId + 1,
                'limit' => 10,
                'timeout' => 60
            ],
            65
        );
        if (!$response) {
            return null;
        }
        return $response['result'];
    }

    /**
     * @see https://core.telegram.org/bots/api#answerinlinequery
     * @param int $queryId
     * @param string $title
     * @param string $description
     * @param string $text
     * @param string|null $url
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function answerInlineQuery(int $queryId, string $title, string $description, string $text, ?string $url = null): mixed
    {
        return $this->postJson(
            "answerInlineQuery",
            array_filter([
                'inline_query_id' => $queryId,
                'button' => !$url ? null : [
                    'text' => 'Open!',
                    'web_app' => [
                        'url' => $url,
                    ],
                ],
                'results' => [
                    [
                        'type' => 'article',
                        'id' => $queryId,
                        'title' => $title,
                        'description' => $description,
                        'input_message_content' => [
                            'message_text' => $text ?: '😊',
                        ],
                        'hide_url' => true,
                    ]
                ],
            ]),
        );
    }

    /**
     * @see https://core.telegram.org/bots/api#sendmessage
     * @param int $chatId
     * @param string $text
     * @param array|null $entities
     * @param array|null $replyMarkup
     * @return array|null
     */
    public function sendMessage(int $chatId, string $text, array $entities = null, array $replyMarkup = null)
    {
        $response = $this->postJson(
            "sendMessage",
            array_filter([
                'chat_id' => $chatId,
                'text' => $text,
                'entities' => $entities,
                'reply_markup' => $replyMarkup,
            ]),
        );

        return $response;
    }

    /**
     * @see https://core.telegram.org/bots/api#answercallbackquery
     *
     * @param int $callbackId
     * @param string $text
     * @param bool $showAlert
     * @return array|null
     */
    public function answerCallbackQuery(int $callbackId, string $text, bool $showAlert = true)
    {
        $response = $this->postJson(
            "answerCallbackQuery",
            [
                'callback_query_id' => $callbackId,
                'text' => $text,
                'show_alert' => $showAlert,
            ],
        );

        return $response;
    }

    /**
     * Get the last processed event id
     *
     * @return int
     */
    public function getLastProcessedUpdateId(): int
    {
        $lastId = @file_get_contents($this->cursorFileName);
        if ($lastId === false || $lastId === null || !is_numeric(trim($lastId))) {
            $lastId = $this->setLastProcessedUpdateId(0);
        }
        return $lastId;
    }

    /**
     * Set last processed update id.
     * Each time you request for Updates you should send the last processed event id.
     *
     * @param int $updateId
     * @return int
     */
    public function setLastProcessedUpdateId(int $updateId): int
    {
        $filePath = $this->cursorFileName;
        $result = @file_put_contents($filePath, $updateId);
        if ($result) {
            return $updateId;
        }
        $user = $_SERVER['USERNAME'] ?? $_SERVER['USER'] ?? '';
        $errorText = "Cannot save file '{$filePath}'.";
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            throw new ClientSafeException("{$errorText} Data directory '{$dir}' does not exists");
        }
        if (!is_writable($dir)) {
            throw new ClientSafeException("{$errorText} Data directory '{$dir}' exists but not writable for user '{$user}'");
        }
        if (file_exists($filePath)) {
            if (!is_writable($filePath)) {
                throw new ClientSafeException("{$errorText} File exists but not writable for user '{$user}'");
            }
        }
        $freeSpace = disk_free_space($dir);
        if (false !== $freeSpace && $freeSpace < 1024) {
            throw new ClientSafeException("{$errorText} No free space on disk");
        }
        throw new ClientSafeException("{$errorText} Directory exists, directory permissions are OK, free disk space is OK. Unknown failure reason.");
    }

    /**
     * Internal method providing basic HTTP-requests routines
     *
     * @param string $apiMethod
     * @param array|null $data
     * @param int $timeout
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function postJson(string $apiMethod, ?array $data = null, int $timeout = 30): ?array
    {
        if ($this->logger) {
            $this->logger->debug("Telegram API call: {$apiMethod}", [
                'data' => $data,
            ]);
        }

        $response = $this->httpClient->request(
            'POST',
            "https://api.telegram.org/bot{$_ENV['TELEGRAM_TOKEN']}/{$apiMethod}",
            [
                RequestOptions::JSON => $data,
                RequestOptions::CONNECT_TIMEOUT => $timeout,
                RequestOptions::HTTP_ERRORS => false,
            ]
        );
        $decoded = json_decode($response->getBody(), true);
        if (401 == $response->getStatusCode()) {
            throw new ClientSafeException("Bot token is set, but invalid");
        }
        if (400 == $response->getStatusCode()) {
            throw new ClientSafeException("Telegram error: " . ($decoded['description'] ?? 'unknown'));
        }
        if (200 != $response->getStatusCode()) {
            return null;
        }
        if (!$decoded || empty($decoded['ok'])) {
            return null;
        }
        return $decoded;
    }
}
