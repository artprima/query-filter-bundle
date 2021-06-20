<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\QueryFilter\Config;

/**
 * Class Alias.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
final class Alias
{
    /**
     * Alias constructor.
     *
     * @param string|null $name alias name, eg.: fullname
     * @param string|null $expr expression, eg.: concat(concat(concat(concat(p.firstname, ' '), p.middlename), ' '), p.lastname)
     */
    public function __construct(private ?string $name = null, private ?string $expr = null)
    {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): Alias
    {
        $this->name = $name;

        return $this;
    }

    public function getExpr(): ?string
    {
        return $this->expr;
    }

    public function setExpr(string $expr): Alias
    {
        $this->expr = $expr;

        return $this;
    }
}
