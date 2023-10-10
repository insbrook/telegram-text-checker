<?php

namespace TelegramApp\Framework;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TelegramApp\App\Controllers\ControllerInterface;
use TelegramApp\Framework\Exception\BotException;
use TelegramApp\Framework\Exception\ClientSafeException;
use TelegramApp\Framework\Http\Response\HtmlResponse;
use Throwable;

class App implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected ResponseInterface | null $response = null;

    /**
     * Compare request against routes,
     * next get controller from the container
     * to obtain a response
     *
     * @param ContainerInterface $container
     * @param array $routes
     * @return ResponseInterface|null
     */
    public function run(ContainerInterface $container, array $routes): ?ResponseInterface
    {
        if (empty($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = '/' . ($_SERVER['argv'][1] ?? '');
        }

        try {
            return $this->dispatch($container, $routes, $_SERVER['REQUEST_URI']);
        } catch (ClientSafeException $ex) {
            return (new HtmlResponse($ex->getCode() ?: 200))->withData($ex->getMessage());
        } catch (Throwable $ex) {
            if ($this->logger) {
                $this->logger->error($ex->getMessage(), [
                    'uri' => $_SERVER['REQUEST_URI'],
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine(),
                ]);
            }
            return (new HtmlResponse(500))->withData('Runtime server error');
        }
    }

    /**
     * Get the first suitable controller and ask it to handle the request
     *
     * @param ContainerInterface $container
     * @param array $routes
     * @param string $requestUri
     * @return ResponseInterface|null
     */
    protected function dispatch(ContainerInterface $container, array $routes, string $requestUri): ?ResponseInterface
    {
        $request = ServerRequest::fromGlobals();
        if (str_contains($request->getHeaderLine('Content-Type'), 'json')) {
            $request = $request->withParsedBody(json_decode($request->getBody(), true));
        }

        foreach ($routes as $path => $handler) {
            if (!str_starts_with($_SERVER['REQUEST_URI'] ?? '', $path)) {
                continue;
            }
            $handlerClass = $container->get($handler);
            assert($handlerClass instanceof ControllerInterface);
            return $handlerClass->handleRequest($request);
        }
        throw new BotException('Unknown command exception', 404);
    }
}
