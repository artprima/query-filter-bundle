<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query;

/**
 * Class Filter.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class Filter
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $connector = 'and';

    /**
     * @var bool
     */
    private $having = false;

    private $x;
    private $y;

    private $extra;

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): Filter
    {
        $this->field = $field;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Filter
    {
        $this->type = $type;

        return $this;
    }

    public function getConnector(): string
    {
        return $this->connector;
    }

    public function setConnector(string $connector): Filter
    {
        $this->connector = $connector;

        return $this;
    }

    public function isHaving(): bool
    {
        return $this->having;
    }

    public function setHaving(bool $having): Filter
    {
        $this->having = $having;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @param mixed $x
     */
    public function setX($x): Filter
    {
        $this->x = $x;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @param mixed $y
     */
    public function setY($y): Filter
    {
        $this->y = $y;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @param mixed $extra
     */
    public function setExtra($extra): Filter
    {
        $this->extra = $extra;

        return $this;
    }
}
