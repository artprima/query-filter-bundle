<?php declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query;

/**
 * Class Filter
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query
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

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     * @return Filter
     */
    public function setField(string $field): Filter
    {
        $this->field = $field;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Filter
     */
    public function setType(string $type): Filter
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getConnector(): string
    {
        return $this->connector;
    }

    /**
     * @param string $connector
     * @return Filter
     */
    public function setConnector(string $connector): Filter
    {
        $this->connector = $connector;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHaving(): bool
    {
        return $this->having;
    }

    /**
     * @param bool $having
     * @return Filter
     */
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
     * @return Filter
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
     * @return Filter
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
     * @return Filter
     */
    public function setExtra($extra): Filter
    {
        $this->extra = $extra;

        return $this;
    }
}