# Backend

[&larr; Back](./README.md)

To make it easier for developers to use the entire project or parts of it
in other projects, all components are as compatible as possible with PSR standards.

## Contents

- [Minimum requirements](#reqs)
- [Setting up bot backend](#setup)
- [Under the hood](#hood)
- [Logging](#logging)
- [Development and Debugging](#debug)

## <a name="reqs"></a>Minimum requirements

- PHP8.1 or newer with curl, json and mbstring modules.
- Composer dependency manager.

Installation how-tos:
- [How to install PHP](https://www.php.net/manual/en/install.php)
- [Composer for PHP installation](https://getcomposer.org/download/)

## <a name="setup"></a>Setting up bot backend

You should have installed all the required software from [Mininum requirements](#reqs) section.
Clone this repository and install required backend dependencies using `composer install` command from the root of the project directory or `./composer.phar install`
for stand-alone installation.

All bot settings are configured from `.env` file. To create one just copy `.env-sample` file to `.env` file.
Next open `.env` file and set your values.

- `TELEGRAM_TOKEN` is an API Token, which can be obtained from [BotFather](https://t.me/BotFather) in your bot settings.
- `POLLING_LIFETIME` - *optional* parameter. Max console polling command lifetime. Use it if you use cron for scheduled poller execution.
- `TEXTGEARS_CHECKER_API_KEY` - *optional* parameter. Required if you plan to use TextGears API for text analyzis purposes.

After filling `.env` file run check to be sure everything is fine.
`php public/index.php status` or open web root in browser to check web server.

Any time you can run this command to check for bot health to prevent any possible runtime errors.

##### Possible errors and the ways to fix it:

| Error                                                                                                         | How to fix                                                                                                                                              |
|---------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------|
| Telegram bot token not set. Env file not exists                                                     | `.env` file was not found on project root directory.                                                                                                    |
| Telegram bot token not set. Env file exists, but user 'www-data' have no reading access to the file | `.env` file exists, but cannot be read. Check file and user permissions.                                                                                |
| Telegram bot token not set. Check your .env file                                                    | `.env` file found and read. But no token found. Set the token or check file format for errors.                                                          |
| Bot token is set, but invalid                                                                       | Set an actual bot API token from [BotFather](https://t.me/BotFather).                                                                                   |
| Cannot save file '...'. Data directory '...' does not exists                                        | Bot need to save some runtime variables to files located in `data` directory on the project root. The directory must exist and be writable for the bot. |
| Cannot save file '...'. Data directory '...' exists but not writable for user 'www-data'            | Data directory must be writable for the bot. Change a directory and its contents owner or set a wider permissions to allow writing.                     |
| Cannot save file '...'. File exists but not writable for user 'www-data'           | Bot runtime settings file access is restricted. Change a file owner or set a wider permissions to allow writing.                                        |
| Cannot save file '...'. No free space on disk           | Looks like your disk is full. The bot requires some free space to work properly. Please remove some less useful files than the bot`s files.             |
| Cannot save file '...'. Directory exists, directory permissions are OK, free disk space is OK. Unknown failure reason.           | Check for the number of free inodes and disk health.                                                                                                    |

##### How does success look?

I do know the answer. The answer is:

```
{
    "Time": "2023-10-07T13:19:11+00:00",
    "Token": "OK",
    "Username": "TextGearsBot",
    "WebHook": "Not set",
    "Last processed update id": 403194207,
    "Everything is OK": "Yes, sir!"
}
```

The basic bot functionality is ready.

Run `php public/index.php poll` to start polling Telegram for incoming bot events and process it!

## <a name="hood"></a> Under the hood

### Framework

What is a backend app? Service container providing dependency injection + request processing dispatcher.

All common classes are located at `src/Framework`.

`TelegramApp\Framework\Container` is a service container. It is configured with 
a very simple way from `src\services.php` config. Have look!

To process any request `TelegramApp\Framework\App` gets a Controller from the container 
according to routing params. Next `App` creates an instance of request and ask controller to handle it
to get a response. All the code is rather simple yet PSR-compatible.

### Application

The application provides two kinds of funcionality.
Telegram communication routines and text checking.

#### Telegram interconnection

Run `php public/index.php poll` to start polling Telegram to get and handle bot updates.

You can make a system daemon to restart the command on exit or schedule it with `crontab -e` for example every 10 minutes.
To limit poller lifetime with 10 minutes interval, set timout in seconds in `.env` file  `POLLING_LIFETIME=600`

Telegram updates handling is situated in `TelegramApp\App\Controllers\Poll` controller.
You can edit existing handlers or create a new one for another update type. 

```php
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
```

## <a name="logging"></a>Logging

This app provides a PSR-compatible logging mechanics. To enable logging on any class
just add `implements LoggerAwareInterface` and `use LoggerAwareTrait;` by PSR.
On creating an instance of such interface `Container` will automatically set logger to the object.
And the logger will be available for your purposes:

```php
if ($this->logger) {
    $this->logger->debug("Telegram API call: {$apiMethod}", [
        'data' => $data,
    ]);
}
```

Where do logger saves its info? Have a look at service container config file `src/services.php`.
```php
LoggerInterface::class => Framework\FileLogger::class,
```

and for testing environment in file `test/services.php`:

```php
// Log to an array for debug purposes
LoggerInterface::class => MocksAndFakers\ArrayLogger::class,
```

I implemented a simple file logger and logger to array for testing purposes. You can write your own logger
or use any third-party library. 
And the only thing you should do to change logging provider is to change service config!
PSR is really cool!
```php
// For example
LoggerInterface::class => YourMySqlLogger::class,
```

## <a name="debug"></a>Development and Debugging

App backend is equipped with unit-tests for validating and debugging purposes.
Mocks and fakers are at your service!

**If you have never tried unit-tests, today is the best day to start.**

How to run unit-tests?

`./vendor/bin/phpunit`


How to run a single unit-test?

`./vendor/bin/phpunit ./test/App/Controller/StatusTest.php`
