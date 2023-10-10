<?php

namespace TelegramApp\App\Handlers;

use TelegramApp\App\I18n\Translator;
use TelegramApp\App\TelegramClient;
use TelegramApp\App\TextChecker\Exceptions\UnsupportedLanguageException;
use TelegramApp\App\TextChecker\TextCheckingProvider;

class MessageHandler implements UpdateHandlerInterface
{
    public function __construct(
        protected TextCheckingProvider $textCheckingProvider,
        protected Translator $translator
    ) {
        //
    }

    public function handle(TelegramClient $telegramClient, int $updateId, array $updateInfo): mixed
    {
        $lang = $updateInfo['message']['from']['language_code'] ?? Translator::DEFAULT_LANG;
        $this->translator->setLang($lang);

        $text = $updateInfo['message']['text'] ?? '';
        if (str_starts_with($text, '/start')) {
            $telegramClient->sendMessage(
                $updateInfo['message']['chat']['id'],
                $this->translator->text('welcome')
            );
            return true;
        }
        // On sticker send emoji
        if (!empty($updateInfo['message']['sticker'])) {
            $emojis = ['👍', '☺', '️😱', '😁', '😳', '🙃'];
            if (str_contains($updateInfo['message']['sticker']['set_name'] ?? '', 'cat')) {
                $emojis = [ '😸', '😻', '😼', '😽', '😺'];
            }
            shuffle($emojis);
            $telegramClient->sendMessage(
                $updateInfo['message']['chat']['id'],
                $emojis[0]
            );
            return true;
        }

        try {
            $info = $this->textCheckingProvider->getTextWithErrors($text);
        } catch (UnsupportedLanguageException $ex) {
            $info = [
                'text' => $this->translator->text($ex->getMessage())
            ];
        }

        $buttons = [
            'inline_keyboard' => [
                [
                    [
                        'callback_data' => 'correct',
                        'text' => $this->translator->text('show_corrected'),
                    ],
                ]
            ],
        ];

        if (empty($info['entities'])) {
            $info['text'] = '✅';
            $buttons = null;
        }

        $telegramClient->sendMessage(
            $updateInfo['message']['chat']['id'],
            $info['text'],
            $info['entities'] ?? null,
            $buttons
        );
        return true;
    }
}
