<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Response;

/**
 * Interface ResponseInterface
 *
 * @author Denis Voytyuk <denis@voituk.ru>
 *
 * @package Artprima\QueryFilterBundle\Response
 */
interface ResponseInterface
{
    public function getData();
    public function getMeta(): array;
    public function setData($data): ResponseInterface;
    public function setMeta(array $meta): ResponseInterface;
    public function addMeta(string $field, $value): ResponseInterface;
}
