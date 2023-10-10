<?php

namespace TelegramApp\Framework\Http\Response;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class HtmlResponse extends Response implements ResponseInterface
{
    protected string $data = '';

    /**
     * @param mixed $data
     * @return HtmlResponse
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
        return Utils::streamFor((string)$this->data);
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
            ->withAddedHeader('Content-Type', 'text/html')
            ->getHeaders();
    }
}
