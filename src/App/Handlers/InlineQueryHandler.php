<?php

namespace TelegramApp\App\Handlers;

use TelegramApp\App\I18n\Translator;
use TelegramApp\App\TelegramClient;
use TelegramApp\App\TextChecker\TextCheckingProvider;

class InlineQueryHandler implements UpdateHandlerInterface
{
    public function __construct(
        protected TextCheckingProvider $textCheckingProvider,
        protected Translator $translator
    ) {
        //
    }

    public function handle(TelegramClient $telegramClient, int $updateId, array $updateInfo): mixed
    {
        $lang = $updateInfo['inline_query']['from']['language_code'] ?? Translator::DEFAULT_LANG;
        $this->translator->setLang($lang);

        $text = $updateInfo['inline_query']['query'] ?? '';
        $text = $this->textCheckingProvider->getCorrected($text, $foundErrors);

        $telegramClient->answerInlineQuery(
            $updateInfo['inline_query']['id'],
            $this->translator->text($foundErrors ? 'send_corrected' : 'send_as_it_is'),
            $this->translator->text($foundErrors ? 'found_some_errors' : 'no_errors_found'),
            $text
        );
        return true;
    }
}
