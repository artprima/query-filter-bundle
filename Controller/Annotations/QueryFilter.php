<?php

declare(strict_types=1);

namespace Artprima\QueryFilterBundle\Controller\Annotations;

use Attribute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class QueryFilter.
 *
 * @author Denis Voytyuk <ask@artprima.cz>
 * @Annotation
 */
#[Attribute(Attribute::TARGET_METHOD)]
class QueryFilter extends Template
{
    public function getAliasName(): string
    {
        return 'queryfilter';
    }
}
