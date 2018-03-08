| [Master][Master] |
|:----------------:|
| [![Build Status][Master image]][Master] |
| [![Coverage Status][Master coverage image]][Master coverage] |
| [![Quality Status][Master quality image]][Master quality] |

Query Filter Bundle
===================

Query Filter Bundle brings request filtering and pagination functionality to Symfony 4 applications that use Doctrine 2.

# Installation

First, install the dependency:

```bash
$ composer require artprima/query-filter-bundle
```

# Usage examples

## Basic example

* Controller

```php
<?php

namespace App\Controller;

use Artprima\QueryFilterBundle\QueryFilter\Config\BaseConfig; 
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Artprima\QueryFilterBundle\Request\Request;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilter;
use Artprima\QueryFilterBundle\Response\Response;
use App\Repository\ItemRepository;

class DefaultController extends Controller
{
    // ...
    
    /**
     * @Route("/") 
     */
    public function indexAction(HttpRequest $request, ItemRepository $repository)
    {
        // set up the config
        $config = new BaseConfig();
        $config->setSearchAllowedCols(['t.name']);
        $config->setAllowedLimits([10, 25, 50, 100]);
        $config->setDefaultLimit(10);
        $config->setSortCols(['t.id'], ['t.id' => 'asc']);
        $config->setRequest(new Request($request));
        
        // here we provide a repository callback that will be used internally in the QueryFilter
        // The signature of the method must be as follows: function functionName(QueryFilterArgs $args): QueryResult;
        $config->setRepositoryCallback([$repository, 'findByOrderBy']);
        
        // Response must implement Artprima\QueryFilterBundle\Response\ResponseInterface
        $queryFilter = new QueryFilter(Response::class);
        /** @var Response $data the type of the variable is defined by the class in the first argument of QueryFilter's constructor */
        $response = $queryFilter->getData($config);
        $data = $response->getData();
        $meta = $response->getMeta();
        
        // ... now do something with $data or $meta
    }
    
    // ...
}
```

* Repository

```php
<?php

namespace App\Repository;

use Artprima\QueryFilterBundle\Query\ProxyQueryBuilder;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilterArgs;
use Artprima\QueryFilterBundle\QueryFilter\QueryResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class ItemRepository extends ServiceEntityRepository
{
    // ...
    
    public function findByOrderBy(QueryFilterArgs $args): QueryResult
    {
        // Build our request
        $qb = $this->createQueryBuilder('t')
            ->setFirstResult($args->getOffset())
            ->setMaxResults($args->getLimit());

        // Use QueryFilterBundle provided QueryBuilder wrapper
        $proxyQb = new ProxyQueryBuilder($qb, /* $calcRows = */ true);
        $query = $proxyQb->getSortedAndFilteredQuery($args->getSearchBy(), $args->getSortBy());
        $result = $query->getResult();
        
        // we can do that ProxyQueryBuilder was set to calculate rows (MySQL/MariaDB only!)
        // otherwise we should calculate the rows manually
        $totalRows = $this->getEntityManager()->getConnection()->query('SELECT FOUND_ROWS()')->fetchColumn();
        
        // return the wrapped result
        return new QueryResult($result, $totalRows);
    }    
    
    // ...
}
```

Now you can start your php server and filter the requests:

```
GET http://127.0.0.1:8000/?filter[t.name]=Doe&limit=100
```

This request will perform a LIKE request in DQL: 

```sql
SELECT t FROM Item WHERE t.name LIKE "%Doe%" LIMIT 100
``` 

## Advanced example

This filtering library is best used together with JMSSerializerBundle and FOSRestBundle. You will eventually write a lot less code that it was shown in the basic example.

To utilize the advanced usage, install all the packages.

```bash
composer require friendsofsymfony/rest-bundle
composer require jms/serializer-bundle
composer require artprima/query-filter-bundle
```

Turn them on in `config/bundles.php`:

```php
<?php

return [
    // ...
    Artprima\QueryFilterBundle\ArtprimaQueryFilterBundle::class => ['all' => true],
    FOS\RestBundle\FOSRestBundle::class => ['all' => true],
    JMS\SerializerBundle\JMSSerializerBundle::class => ['all' => true],
    // ...
];
```

_NOTE: you may need to add further bundles depending on your set up for FOSRestBundle and/or JMSSerializerBundle._

Controller:

```php
<?php

namespace App\Controller;

use App\QueryFilter\Response;
use App\Repository\RfmEntryRepository;
use Artprima\QueryFilterBundle\QueryFilter\Config\ConfigInterface as QueryFilterConfigInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Artprima\QueryFilterBundle\Controller\Annotations\QueryFilter;

class ItemController extends FOSRestController implements ClassResourceInterface
{
    /**
     * @Rest\View(serializerEnableMaxDepthChecks=true)
     * @ParamConverter("config", class="App\QueryFilter\Config\ItemConfig",
     *                           converter="query_filter_config_converter",
     *                           options={"entity_class": "App\Entity\Item", "repository_method": "findByOrderBy"})
     * @QueryFilter()
     * @Rest\Get("/items")
     */
    public function cgetAction(QueryFilterConfigInterface $config)
    {
        return $config;
    }
}
``` 

Repository:

```php
<?php

namespace App\Repository;

use App\Entity\Item;
use Artprima\QueryFilterBundle\Query\ProxyQueryBuilder;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilterArgs;
use Artprima\QueryFilterBundle\QueryFilter\QueryResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function findByOrderBy(QueryFilterArgs $args): QueryResult
    {
        $qb = $this->createQueryBuilder('t')
            ->setFirstResult($args->getOffset())
            ->setMaxResults($args->getLimit());

        $proxyQb = new ProxyQueryBuilder($qb);
        $query = $proxyQb->getSortedAndFilteredQuery($args->getSearchBy(), $args->getSortBy());
        $result = $query->getResult();
        $totalRows = $this->getEntityManager()->getConnection()->query('SELECT FOUND_ROWS()')->fetchColumn();

        return new QueryResult($result, $totalRows);
    }
}
```

ItemConfig:

```php
<?php

namespace App\QueryFilter\Config;

use Artprima\QueryFilterBundle\QueryFilter\Config\BaseConfig;

class RfmConfig extends BaseConfig
{
    public function __construct()
    {
        $this->setSearchAllowedCols(array(
            't.name',
        ));

        $this->setSortCols(
            array(
                't.id',
            ),
            array('t.id' => 'desc') // default
        );
    }
}
```

_This document is not finished yet, more examples will follow_.

# Code license

You are free to use the code in this repository under the terms of the MIT license. LICENSE contains a copy of this license.

  [Master image]: https://travis-ci.org/artprima/query-filter-bundle.svg?branch=master
  [Master]: https://travis-ci.org/artprima/query-filter-bundle
  [Master coverage image]: https://img.shields.io/scrutinizer/coverage/g/artprima/query-filter-bundle/master.svg?style=flat-square
  [Master coverage]: https://scrutinizer-ci.com/g/artprima/query-filter-bundle/?branch=master
  [Master quality image]: https://img.shields.io/scrutinizer/g/artprima/query-filter-bundle/master.svg
  [Master quality]: https://scrutinizer-ci.com/g/artprima/query-filter-bundle/?branch=master
