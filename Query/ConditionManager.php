<?php declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Query;

use Artprima\QueryFilterBundle\Query\Condition\ConditionInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ConditionManager
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class ConditionManager implements \ArrayAccess, \Iterator
{
    /**
     * @var ConditionInterface[]
     */
    private array $conditions = [];

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

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->conditions);
    }

    public function offsetGet($offset): mixed
    {
        return $this->offsetExists($offset) ? $this->conditions[$offset] : null;
    }

    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->conditions[] = $value;
        } else {
            $this->conditions[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void {
        unset($this->conditions[$offset]);
    }

    public function rewind(): void {
        reset($this->conditions);
    }

    public function current(): mixed {
        return current($this->conditions);
    }

    public function key(): mixed {
        return key($this->conditions);
    }

    public function next(): void {
        next($this->conditions);
    }

    public function valid(): bool {
        return key($this->conditions) !== null;
    }
}
