<?php declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query;

use Artprima\QueryFilterBundle\Query\Condition\ConditionInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ConditionManager
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 *
 * @package Artprima\QueryFilterBundle\Query
 */
class ConditionManager implements \ArrayAccess, \Iterator
{
    /**
     * @var ConditionInterface[]
     */
    private $conditions = [];

    public function wrapQueryBuilder(QueryBuilder $qb): ProxyQueryBuilder
    {
        return new ProxyQueryBuilder($qb, $this);
    }

    public function add(ConditionInterface $condition, string $name): void
    {
        $this->conditions[$name] = $condition;
    }

    /**
     * @return ConditionInterface[]
     */
    public function all(): array
    {
        return $this->conditions;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->conditions);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->conditions[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->conditions[] = $value;
        } else {
            $this->conditions[$offset] = $value;
        }
    }

    public function offsetUnset($offset) {
        unset($this->conditions[$offset]);
    }

    public function rewind() {
        return reset($this->conditions);
    }

    public function current() {
        return current($this->conditions);
    }

    public function key() {
        return key($this->conditions);
    }

    public function next() {
        return next($this->conditions);
    }

    public function valid() {
        return key($this->conditions) !== null;
    }
}
