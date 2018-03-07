<?php

namespace Artprima\QueryFilterBundle\Response;

/**
 * Class Response
 *
 * @author Denis Voytyuk <denis.voytyuk@feedo.cz>
 */
class Response implements ResponseInterface
{
    /**
     * @var mixed filtered data
     */
    private $data;

    /**
     * @var array meta data (e.g. pagination info)
     */
    private $meta;

    public function __construct($data = null, array $meta = array())
    {
        $this->data = $data;
        $this->meta = $meta;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): ResponseInterface
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
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
