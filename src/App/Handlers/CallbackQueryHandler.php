<?php

namespace TelegramApp\App\Handlers;

use TelegramApp\App\I18n\Translator;
use TelegramApp\App\TelegramClient;
use TelegramApp\App\TextChecker\Exceptions\UnsupportedLanguageException;
use TelegramApp\App\TextChecker\TextCheckingProvider;

class CallbackQueryHandler implements UpdateHandlerInterface
{
    public function __construct(
        protected TextCheckingProvider $textCheckingProvider,
        protected Translator $translator,
    ) {
        //
    }

    public function handle(TelegramClient $telegramClient, int $updateId, array $updateInfo): mixed
    {
        $buttons = null;

        $lang = $updateInfo['callback_query']['from']['language_code'] ?? Translator::DEFAULT_LANG;
        $this->translator->setLang($lang);

        $text = $updateInfo['callback_query']['message']['text'] ?? '';

        try {
            if ('correct' == $updateInfo['callback_query']['data']) {
                // Restore initial text
                $entities = $updateInfo['callback_query']['message']['entities'];
                foreach (array_reverse($entities) as $entity) {
                    if ($entity['type'] != 'bold') {
                        continue;
                    }
                    $text = mb_substr($text,0, $entity['offset']) . mb_substr($text, $entity['offset'] + $entity['length']);
                }
                $info = [
                    'text' => $this->textCheckingProvider->getCorrected($text),
                ];
            } else {
                $info = [
                    'text' => $text,
                ];
            }
        } catch (UnsupportedLanguageException $ex) {
            $info = [
                'text' => $this->translator->text($ex->getMessage())
            ];
        }

        $telegramClient->sendMessage(
            $updateInfo['callback_query']['message']['chat']['id'],
            $info['text'],
            $info['entities'] ?? null,
            $buttons
        );

        $telegramClient->answerCallbackQuery(
            $updateInfo['callback_query']['id'],
            'OK',
            false
        );

        return true;
    }
}
