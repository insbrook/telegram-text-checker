<?php

namespace TelegramApp\Framework;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use TelegramApp\Framework\Exception\BotException;
use Closure;

/**
 * Service container pattern implementation.
 *
 * Stores instances of classes and closures for their creation
 */
class Container implements ContainerInterface
{
    protected array $services = [];

    public function __construct(array $dependenciesMap = [])
    {
        $this->setFromDependenciesMap($dependenciesMap);
    }

    public function get(string $id)
    {
        if ($id === static::class) {
            return $this;
        }

        if (!$this->has($id)) {
            throw new BotException("Container cannot find '{$id}'");
        }

        /*
         * You can instantiate class with a closure
         */
        if ($this->services[$id] instanceof Closure) {
            $closure = $this->services[$id];
            $this->services[$id] = $closure();
        }

        /*
         * Or set an invocable factory to make a new object each time
         */
        if (is_callable($this->services[$id])) {
            $closure = $this->services[$id];
            $service = $closure($this);
        } else {
            $service = $this->services[$id];
        }

        // Set logger to each LoggerAwareInterface
        if ($service instanceof LoggerAwareInterface) {
            $service->setLogger($this->get(LoggerInterface::class));
        }

        return $service;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->services);
    }

    public function set(string $id, $value = null)
    {
        $this->services[$id] = $value;
    }

    /**
     * You can configure services dependencies using a simple file with clear syntax
     *
     * @param array $config
     * @return void
     */
    public function setFromDependenciesMap(array $config)
    {
        foreach ($config as $class => $parameters) {

            /*
             * Set up constructor parameters
             *
             * Initial config sample:
             *
             * // Classname will be constructed with three params: two objects and an array
             * TextChecker\Classname::class => [
             *   LanguageDetection\Language::class,
             *   ContainerInterface::class,
             *   [
             *       // Allow-list of supported languages
             *       'en-US', 'en-GB', 'en-ZA', 'en-AU', 'en-NZ', 'fr-FR', 'de-DE', 'de-AT', 'de-CH',
             *       'pt-PT', 'pt-BR', 'it-IT', 'ar-AR', 'ru-RU', 'es-ES', 'ja-JP', 'zh-CN', 'el-GR'
             *   ]
             * ],
             *
             * // Sample alias usage
             * ClientInterface::class => Client::class,
             *
             * we should put a closure into container which returns
             * Classname, constructed with parameters:
             * instance of LanguageDetection\Language, instance of ContainerInterface,
             * and an array of strings
             */
            $this->set($class, function () use ($class, $parameters) {
                if (is_string($parameters)) {
                    // Setting an alias
                    return $this->get($parameters);
                }
                foreach ($parameters as &$constructorParameter) {
                    if (!is_string($constructorParameter)) {
                        continue;
                    }
                    $constructorParameter = $this->get($constructorParameter);
                }
                $className = $class;
                $result = new $className(...$parameters);
                // Set instance instead of callable
                $this->set($class, $result);
                return $result;
            });
        }
    }
}
