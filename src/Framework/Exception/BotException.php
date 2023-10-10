<?php

namespace TelegramApp\Framework\Exception;

use RuntimeException;

/**
 * Basic exception.
 * All exceptions must inherit this one to separate exceptions thrown with bot and with internal libraries.
 *
 * Response processor hides messages of this exception. If you need to throw an exception
 * to say anything to user, @see ClientSafeException instead
 */
class BotException extends RuntimeException
{
    protected $message = 'Unhandled backend error';
}
