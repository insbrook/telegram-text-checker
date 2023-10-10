<?php

namespace TelegramApp\App\TextChecker\Exceptions;

use TelegramApp\Framework\Exception\ClientSafeException;

class UnsupportedLanguageException extends ClientSafeException
{
    public const I18N = 'exception_unsupported_lang';

    protected $message = self::I18N;
}
