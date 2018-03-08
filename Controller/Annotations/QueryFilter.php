<?php

declare(strict_types=1);

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
    protected $serializedData = false;
    /**
     * @return string
     */
    public function getAliasName()
    {
        return 'queryfilter';
    }

    /**
     * @param bool $serializedData
     */
    public function setSerializedData($serializedData)
    {
        $this->serializedData = $serializedData;
    }

    /**
     * @return bool
     */
    public function hasSerializedData()
    {
        return $this->serializedData;
    }
}
