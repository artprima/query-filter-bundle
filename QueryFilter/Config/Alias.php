<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\QueryFilter\Config;

/**
 * Class Alias
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\QueryFilter\Config
 */
class Alias
{
    /**
     * @var string alias name, eg.: fullname
     */
    private $name;

    /**
     * @var string expression, eg.: concat(concat(concat(concat(p.firstname, ' '), p.middlename), ' '), p.lastname)
     */
    private $expr;

    public function __construct(string $name = null, string $expr = null)
    {
        $this->name = $name;
        $this->expr = $expr;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Alias
     */
    public function setName(string $name): Alias
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getExpr(): string
    {
        return $this->expr;
    }

    /**
     * @param string $expr
     * @return Alias
     */
    public function setExpr(string $expr): Alias
    {
        $this->expr = $expr;

        return $this;
    }
}
