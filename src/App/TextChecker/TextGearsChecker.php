<?php

namespace TelegramApp\App\TextChecker;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use TelegramApp\App\TextChecker\Exceptions\UnsupportedLanguageException;
use TelegramApp\Framework\Exception\ClientSafeException;

class TextGearsChecker implements TextCheckerInterface
{
    public function __construct(
        protected ClientInterface $httpClient
    ) {
        //
    }

    /**
     * You should get your own key on
     * @see https://textgears.com/signup?shutupandgiveme=thekey
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return !empty($_ENV['TEXTGEARS_CHECKER_API_KEY']);
    }

    /**
     * @param string $text
     * @param string $language
     * @return array|TextError[]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getErrors(string $text, string $language): array
    {
        $response = $this->httpClient->request('POST', 'https://api.textgears.com/grammar', [
            RequestOptions::FORM_PARAMS => [
                'text' => $text,
                'language' => $language,
                'key' => $_ENV['TEXTGEARS_CHECKER_API_KEY'] ?? '',
                'ai' => 1,
            ],
        ]);

        if (200 != $response->getStatusCode()) {
            return [];
        }
        $responseData = json_decode((string)$response->getBody(), true);
        if (empty($responseData['status'])) {
            if (($responseData['error_code'] ?? 0) == 606) {
                throw new UnsupportedLanguageException();
            }
            if (($responseData['error_code'] ?? 0) == 600) {
                throw new ClientSafeException('Incorrect TextGears API key');
            }
            return [];
        }

        $errors = $responseData['response']['errors'];
        // Cast array to special object
        return array_map(function (array $error) {
            return new TextError($error);
        }, $errors);
    }
}