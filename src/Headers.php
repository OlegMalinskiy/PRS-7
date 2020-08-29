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
     * @param string $name
     * @return bool
     */
    public function hasHeader(string $name): bool
    {
        $normalizedName = $this->normalizeName($name);
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
        $normalizedName = $this->normalizeName($name);
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
        $normalizedName = $this->normalizeName($name);
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
        $this->validateHeaderName($name);
        $this->validateHeaderValue($value);

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
     * @param string $name
     */
    public function removeHeader(string $name): void
    {
        $normalizedName = $this->normalizeName($name);
        if (!empty($this->normalizedMap[$normalizedName])) {
            $name = $this->normalizedMap[$normalizedName];
            unset($this->originalHeaders[$name]);
            unset($this->normalizedMap[$normalizedName]);
        }
    }

    /**
     * @param string $name
     */
    protected function validateHeaderName(string $name): void
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Name of header must be not empty.');
        }
    }

    /**
     * @param $value
     */
    protected function validateHeaderValue($value): void
    {
        if (!is_string($value) && !is_array($value)) {
            throw new InvalidArgumentException('Value of header must be a string or an array.');
        }
        if (empty($value)) {
            throw new InvalidArgumentException('Value of header must be not empty.');
        }
    }

    /**
     * @param string $name
     * @return string
     */
    protected function normalizeName(string $name): string
    {
        $name = $this->prepareName($name);
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
     * @param string|array $value
     * @return array
     */
    protected function preprareValue($value): array
    {
        if (is_string($value)) {
            return [trim($value)];
        } else {
            return array_map(fn($item) => trim($item), $value);
        }
    }
}