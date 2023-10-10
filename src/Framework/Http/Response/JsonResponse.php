<?php

namespace TelegramApp\Framework\Http\Response;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class JsonResponse extends Response implements ResponseInterface
{
    protected mixed $data = null;

    /**
     * @param mixed $data
     * @return JsonResponse
     */
    public function withData(mixed $data)
    {
        $new = clone $this;
        $new->data = $data;
        return $new;
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(json_encode($this->data, JSON_PRETTY_PRINT));
    }

    /**
     * @return string[][]
     */
    public function getHeaders(): array
    {
        if ($this->hasHeader('Content-Type')) {
            return parent::getHeaders();
        }
        return $this
            ->withAddedHeader('Access-Control-Allow-Origin', '*')
            ->withAddedHeader('Access-Control-Allow-Methods', 'GET, POST')
            ->withAddedHeader('Access-Control-Allow-Headers', 'X-Requested-With')
            ->withAddedHeader('Content-Type', 'application/json; charset=utf-8')
            ->getHeaders();
    }
}