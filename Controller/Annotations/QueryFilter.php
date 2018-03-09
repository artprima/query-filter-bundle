<?php declare(strict_types = 1);

namespace Artprima\QueryFilterBundle\Controller\Annotations;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class QueryFilter
 *
 * @author Denis Voytyuk <denis@voituk.ru>
 *
 * @package Artprima\QueryFilterBundle\Controller\Annotations

 * @Annotation
 */
class QueryFilter extends Template
{
    /**
     * @return string
     */
    public function getAliasName()
    {
        return 'queryfilter';
    }
}
