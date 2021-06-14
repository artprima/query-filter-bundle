<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter\Config;

/**
 * Class Alias.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Alias
    {
        $this->name = $name;

        return $this;
    }

    public function getExpr(): string
    {
        return $this->expr;
    }

    public function setExpr(string $expr): Alias
    {
        $this->expr = $expr;

        return $this;
    }
}
