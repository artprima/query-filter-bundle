<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Response;

/**
 * Interface ResponseFactoryInterface
 *
 * @author Denis Voytyuk <denis@voituk.ru>
 *
 * @package Artprima\QueryFilterBundle\QueryFilter
 */
interface ResponseFactoryInterface
{
    public function createResponse(): ResponseInterface;
}
