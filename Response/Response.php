<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Response;

/**
 * Class Response.
 *
 * @author Denis Voytyuk <denis.voytyuk@feedo.cz>
 */
final class Response implements ResponseInterface
{
    /**
     * @param mixed $data filtered data
     * @param array $meta meta data (e.g. pagination info)
     */
    public function __construct(private mixed $data = null, private array $meta = [])
    {
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData($data): ResponseInterface
    {
        $this->data = $data;

        return $this;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function setMeta(array $meta): ResponseInterface
    {
        $this->meta = $meta;

        return $this;
    }

    public function addMeta(string $field, $value): ResponseInterface
    {
        $this->meta[$field] = $value;

        return $this;
    }
}
