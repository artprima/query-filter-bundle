<?php declare(strict_types=1);

namespace Tests\Unit\Artprima\QueryFilterBundle\Fixtures\Response;

use Artprima\QueryFilterBundle\Response\Response;

/**
 * Class ResponseConstructorWithRequiredArguments
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 */
class ResponseConstructorWithRequiredArguments extends Response
{
    public function __construct($data, array $meta)
    {
        parent::__construct($data, $meta);
    }
}
