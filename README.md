| [Master][Master] |
|:----------------:|
| [![Build Status][Master image]][Master] |
| [![Coverage Status][Master coverage image]][Master coverage] |
| [![Quality Status][Master quality image]][Master quality] |

Query Filter Bundle for Symfony 5.3 (and later)
=================================================

Query Filter Bundle brings request filtering and pagination functionality to Symfony 5.3 (and later) applications that use Doctrine 2.

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
        $config->setSearchAllowedFields(['t.name']);
        $config->setAllowedLimits([10, 25, 50, 100]);
        $config->setDefaultLimit(10);
        $config->setSortFields(['t.id'], ['t.id' => 'asc']);
        $config->setRequest(new Request($request));
        
        // Throws an UnexpectedValueException when invalid filter column, sort column or sort type is specified
        $config->setStrictColumns(true);
        
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

use App\Entity\Item;
use Artprima\QueryFilterBundle\Query\ConditionManager;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilterArgs;
use Artprima\QueryFilterBundle\QueryFilter\QueryResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ItemRepository extends ServiceEntityRepository
{
    /**
     * @var ConditionManager
     */
    private $pqbManager;

    public function __construct(RegistryInterface $registry, ConditionManager $manager)
    {
        parent::__construct($registry, Item::class);
        $this->pqbManager = $manager;
    }

    
    public function findByOrderBy(QueryFilterArgs $args): QueryResult
    {
        // Build our request
        $qb = $this->createQueryBuilder('t')
            ->setFirstResult($args->getOffset())
            ->setMaxResults($args->getLimit());

        $proxyQb = $this->pqbManager->wrapQueryBuilder($qb);
        $qb = $proxyQb->getSortedAndFilteredQueryBuilder($args->getSearchBy(), $args->getSortBy());
        $query = $qb->getQuery();
        $paginator = new Paginator($query);

        // return the wrapped result
        return new QueryResult($paginator->getIterator()->getArrayCopy(), count($paginator));
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

* Turn them on in `config/bundles.php`:

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

* Controller:

```php
<?php

namespace App\Controller;

use App\QueryFilter\Response;
use App\Repository\ItemRepository;
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

* Repository:

```php
<?php

namespace App\Repository;

use App\Entity\Item;
use Artprima\QueryFilterBundle\Query\ConditionManager;
use Artprima\QueryFilterBundle\QueryFilter\QueryFilterArgs;
use Artprima\QueryFilterBundle\QueryFilter\QueryResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    /**
     * @var ConditionManager
     */
    private $pqbManager;

    public function __construct(RegistryInterface $registry, ConditionManager $manager)
    {
        parent::__construct($registry, Item::class);
        $this->pqbManager = $manager;
    }

    public function findByOrderBy(QueryFilterArgs $args): QueryResult
    {
        $qb = $this->createQueryBuilder('t')
            ->setFirstResult($args->getOffset())
            ->setMaxResults($args->getLimit());

        $proxyQb = $this->pqbManager->wrapQueryBuilder($qb);
        $qb = $proxyQb->getSortedAndFilteredQueryBuilder($args->getSearchBy(), $args->getSortBy());
        $query = $qb->getQuery();
        $paginator = new Paginator($query);

        return new QueryResult($paginator->getIterator()->getArrayCopy(), count($paginator));
    }
}
```

ItemConfig:

```php
<?php

namespace App\QueryFilter\Config;

use Artprima\QueryFilterBundle\QueryFilter\Config\BaseConfig;

class ItemConfig extends BaseConfig
{
    public function __construct()
    {
        $this->setSearchAllowedFields([
            't.name',
        ]);

        $this->setSortFields(
            ['t.id']
        );
        $this->setSortDefaults(
            ['t.id' => 'desc'] // default
        );
    }
}
```

# Simple Query Filter Examples

_NOTE: assume that all the used fields are enabled in the configuration_

* Performs `t.name LIKE `%doe% comparison 
  * http://127.0.0.1:8000/?filter[t.name]=doe
* Performs `t.name = "Doe"` comparison
  * http://127.0.0.1:8000/?filter[t.name][type]=eq&filter[t.name][x]=Doe
* Performs `t.name <> "Doe"` comparison
  * http://127.0.0.1:8000/?filter[t.name][type]=not%20eq&filter[t.name][x]=Doe
* Performs `t.name LIKE "Doe"` comparison
  * http://127.0.0.1:8000/?filter[t.name][type]=like&filter[t.name][x]=Doe
* Performs `t.name NOT LIKE "Doe"` comparison
  * http://127.0.0.1:8000/?filter[t.name][type]=not%20like&filter[t.name][x]=Doe
* Performs `t.frequency BETWEEN 8 AND 10` comparison
  * http://127.0.0.1:8000/?filter[t.name][type]=between&filter[t.frequency][x]=8&filter[t.frequency][x]=10
* Performs `t.frequency NOT BETWEEN 8 AND 10` comparison
  * http://127.0.0.1:8000/?filter[t.name][type]=not%20between&filter[t.frequency][x]=8&filter[t.frequency][x]=10
* Performs `t.frequency > 7` comparison
  * http://127.0.0.1:8000/?filter[t.name][type]=gt&filter[t.frequency][x]=7
* Performs `t.frequency >= 7` comparison
  * http://127.0.0.1:8000/?filter[t.name][type]=gte&filter[t.frequency][x]=7
* Performs `t.frequency IN (1, 2, 3, 4, 5)` comparison
  * http://127.0.0.1:8000/?filter[t.name][type]=in&filter[t.frequency][x]=1,2,3,4,5
* Performs `t.frequency NOT IN (1, 2, 3, 4, 5)` comparison
  * http://127.0.0.1:8000/?filter[t.name][type]=in&filter[t.frequency][x]=1,2,3,4,5
* Performs `t.description IS NULL` comparison
  * http://127.0.0.1:8000/?filter[t.description][type]=is%20null
* Performs `t.description IS NOT NULL` comparison
  * http://127.0.0.1:8000/?filter[t.description][type]=is%20not%20null
* Performs `t.frequency < 7` comparison
  * http://127.0.0.1:8000/?filter[t.name][type]=lt&filter[t.frequency][x]=7
* Performs `t.frequency <= 7` comparison
  * http://127.0.0.1:8000/?filter[t.name][type]=lte&filter[t.frequency][x]=7
* Combined comparison `t.frequency < 7 AND t.monetary > 50`
  * http://127.0.0.1:8000/?filter[t.frequency][type]=lt&filter[t.frequency][x]=7&filter[t.monetary][type]=gt&filter[t.monetary][x]=50

# Advanced Query Filter Example

Simple mode should be enough for most of the cases, however sometimes we might need to build more complicated filters having one and the same field used.

* Performs `t.frequency = 10 OR t.frequency >= 85` (NOTE: `filter[1][connector]=or` - `connector` can be `and` (default) or `or`; connector used on the first filter has no effect)
  * http://127.0.0.1:8000/?simple=0&filter[0][field]=t.frequency&filter[0][type]=eq&filter[0][x]=10&filter[1][field]=t.frequency&filter[1][type]=gte&filter[1][x]=85&filter[1][connector]=or

# Pagination Examples
* Second page (NOTE: if `page` is not given it defaults to 1)
  * http://127.0.0.1:8000/?page=2
* Limit records to 100 (NOTE: if default limits were provided and `limit` is not within the allowed values, it will be reset to the default value)
  * http://127.0.0.1:8000/?limit=100

# Sorting Example
* Performs `ORDER BY t.userId DESC` (if `sortdir` is not given it defaults to `asc`)
  * http://127.0.0.1:8000/?sortby=t.userId&sortdir=desc

_NOTE: at the moment this bundle doesn't support more than one field for `ORDER BY`._  
  

_This document is not finished yet, more examples will follow_.

# Code license

You are free to use the code in this repository under the terms of the MIT license. LICENSE contains a copy of this license.

  [Master image]: https://travis-ci.org/artprima/query-filter-bundle.svg?branch=master
  [Master]: https://travis-ci.org/artprima/query-filter-bundle
  [Master coverage image]: https://img.shields.io/scrutinizer/coverage/g/artprima/query-filter-bundle/master.svg?style=flat-square
  [Master coverage]: https://scrutinizer-ci.com/g/artprima/query-filter-bundle/?branch=master
  [Master quality image]: https://img.shields.io/scrutinizer/g/artprima/query-filter-bundle/master.svg
  [Master quality]: https://scrutinizer-ci.com/g/artprima/query-filter-bundle/?branch=master
