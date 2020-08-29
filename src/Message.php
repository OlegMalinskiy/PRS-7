<?php

namespace App;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

abstract class Message implements MessageInterface
{
    protected string $protocolVersion = '1.1';
    protected Headers $headers;
    protected StreamInterface $body;

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion($version): self
    {
        $clone = clone $this;
        $clone->protocolVersion = $version;
        return $clone;
    }

    public function getHeaders(): array
    {
        return $this->headers->getHeaders();
    }

    public function hasHeader($name): bool
    {
        return $this->headers->hasHeader($name);
    }

    public function getHeader($name): array
    {
        return $this->headers->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->headers->getHeaderLine($name);
    }

    public function withHeader($name, $value): self
    {
        $clone = clone $this;
        $clone->headers->addHeader($name, $value);
        return $clone;
    }

    public function withAddedHeader($name, $value): self
    {
        $clone = clone $this;
        $clone->headers->addHeader($name, $value, false);
        return $clone;
    }

    public function withoutHeader($name): self
    {
        $clone = clone $this;
        $clone->headers->removeHeader($name);
        return $clone;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): self
    {
        $clone = clone $this;
        $clone->body = $body;
        return $clone;
    }
}
