<?php

/*
 * This file is part of the ${ProjectName} package.
 *
 * (c) Feedo <vyvoj@feedo.cz>
 * (c) Denis Voytyuk <denis@voituk.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
