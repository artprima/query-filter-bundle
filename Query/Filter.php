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
    private string $field;
    private string $type;
    private string $connector = 'and';
    private bool $having = false;
    private mixed $x = null;
    private mixed $y = null;
    private mixed $extra = null;

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

    public function getX(): mixed
    {
        return $this->x;
    }

    public function setX(mixed $x): Filter
    {
        $this->x = $x;

        return $this;
    }

    public function getY(): mixed
    {
        return $this->y;
    }

    public function setY(mixed $y): Filter
    {
        $this->y = $y;

        return $this;
    }

    /**
     * @return mixed value specific per query condition (in most cases unused)
     */
    public function getExtra(): mixed
    {
        return $this->extra;
    }

    public function setExtra(mixed $extra): Filter
    {
        $this->extra = $extra;

        return $this;
    }
}
