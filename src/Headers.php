<?php

namespace App;

use InvalidArgumentException;
use Response;

class Headers
{
    /**
     * @var array
     */
    private array $originalHeaders = [];
    /**
     * @var array
     */
    private array $normalizedMap = [];

    /**
     * @param array $headers
     * @param array $globals
     */
    final public function __construct(array $headers = [], ?array $globals = null)
    {
        $this->globals = $globals ?? $_SERVER;
        $this->setHeaders($headers);
    }
    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        $normalizedName = $this->preprareAndNormalizeName($name);
        return !empty($this->normalizedMap[$normalizedName]);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->originalHeaders;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getHeader(string $name): array
    {
        $normalizedName = $this->preprareAndNormalizeName($name);
        if (!empty($this->normalizedMap[$normalizedName])) {
            $name = $this->normalizedMap[$normalizedName];
            return $this->originalHeaders[$name];
        }
        return [];
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeaderLine(string $name): string
    {
        $normalizedName = $this->preprareAndNormalizeName($name);
        if (!empty($this->normalizedMap[$normalizedName])) {
            $name = $this->normalizedMap[$normalizedName];
            return implode(',', $this->originalHeaders[$name]);
        }
        return '';
    }

    /**
     * @param string $name
     * @param string|array $value
     * @param bool $rewrite
     */
    public function addHeader(string $name, $value, bool $rewrite = true): void
    {
        $this->validateHeader($name, $value);

        $name = $this->prepareName($name);
        $value = $this->preprareValue($value);
        $normalizedName = $this->normalizeName($name);

        if (!empty($this->normalizedMap[$normalizedName])) {
            $name = $this->normalizedMap[$normalizedName];

            if ($rewrite) {
                $this->originalHeaders[$name] = $value;
            } else {
                array_push($this->originalHeaders[$name], ...$value);
            }
        } else {
            $this->normalizedMap[$normalizedName] = $name;
            $this->originalHeaders[$name] = $value;
        }

        if ($this instanceof Response) {
            header(sprintf('%s: %s', $name, $this->getHeaderLine($name)));
        }
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }
        return $this;
    }

    /**
     * @param string $name
     */
    public function removeHeader(string $name): void
    {
        $normalizedName = $this->preprareAndNormalizeName($name);
        if (!empty($this->normalizedMap[$normalizedName])) {
            $name = $this->normalizedMap[$normalizedName];
            unset($this->originalHeaders[$name]);
            unset($this->normalizedMap[$normalizedName]);
        }
    }

    /**
     * @param string $name
     * @return string
     */
    protected function normalizeName(string $name): string
    {
        return strtolower($name);
    }

    /**
     * @param string $name
     * @return string
     */
    protected function prepareName(string $name): string
    {
        return str_replace('_', '-', $name);
    }

    /**
     * @param string $string
     * @return string
     */
    protected function preprareAndNormalizeName(string $string): string
    {
        $name = $this->prepareName($string);
        return $this->normalizeName($name);
    }

    /**
     * @param string|array $value
     * @return array
     */
    protected function preprareValue($value): array
    {
        return is_string($value) ? [trim($value)] : array_map(fn($item) => trim($item), $value);
    }

    /**
     * @param string $name
     * @param string|array $value
     */
    protected function validateHeader(string $name, $value): void
    {
        $this->validateHeaderName($name);
        $this->validateHeaderValue($value);
    }

    /**
     * @param string $name
     */
    protected function validateHeaderName(string $name): void
    {
        if (!is_string($name) || preg_match("@^[!#$%&'*+.^_`|~0-9A-Za-z-]+$@", $name) !== 1) {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
        }
    }

    /**
     * @param string|array $value
     */
    protected function validateHeaderValue($value): void
    {
        if (!is_string($value) && !is_array($value)) {
            throw new InvalidArgumentException('Value of header must be a string or an array.');
        }
        if (empty($value)) {
            throw new InvalidArgumentException('Value of header must be not empty.');
        }

        $items = is_string($value) ? [trim($value)] : array_map(fn($item) => trim($item), $value);

        $pattern = "@^[ \t\x21-\x7E\x80-\xFF]*$@";
        foreach ($items as $item) {
            $hasInvalidType = !is_numeric($item) && !is_string($item);
            $rejected = $hasInvalidType || preg_match($pattern, (string) $item) !== 1;
            if ($rejected) {
                throw new InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
            }
        }
    }

    /**
     * @return static
     */
    public static function createFromGlobals(): self
    {
        $headers = null;

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }

        if (!is_array($headers)) {
            $headers = [];
        }

        return new static($headers);
    }
}
