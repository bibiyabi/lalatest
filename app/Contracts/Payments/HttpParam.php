<?php

namespace App\Contracts\Payments;

class HttpParam
{
    private $url;

    private $method;

    private $header;

    private $body;

    private $orderConfig;

    public function __construct(string $url, string $method, array $header, array $body, array $orderConfig) {
        $this->url = $url;
        $this->method = $method;
        $this->header = $header;
        $this->body = $body;
        $this->orderConfig = $orderConfig;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getOrderConfig()
    {
        return $this->orderConfig;
    }

    public function toArray()
    {
        return [
            'url' => $this->url,
            'method' => $this->method,
            'header' => $this->header,
            'body' => $this->body,
            'orderConfig' => $this->orderConfig,
        ];
    }
}
