<?php declare(strict_types=1);

namespace Tests\Artprima\QueryFilterBundle\Fixtures\Response;

use Artprima\QueryFilterBundle\Response\Response;

/**
 * Class ResponseConstructorWithRequiredArguments
 *
 * @author Denis Voytyuk <denis@voituk.ru>
 *
 * @package Tests\Artprima\QueryFilterBundle\Fixtures\Response
 */
class ResponseConstructorWithRequiredArguments extends Response
{
    public function __construct($data, array $meta)
    {
        parent::__construct($data, $meta);
    }
}
