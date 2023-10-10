<?php

namespace TelegramApp\Framework\Exception;

/**
 * Exception to be shown to user without hiding its message on response processor
 */
class ClientSafeException extends BotException
{
}
