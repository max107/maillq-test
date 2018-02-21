<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\LookupBuilder;

use App\LookupBuilder\Lookup\LookupInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\SchemaTool;

class LookupBuilder
{
    /**
     * @var array
     */
    protected $lookups = [];
    /**
     * @var array
     */
    protected $relations = [];
    /**
     * @var array
     */
    private $joins = [];

    /**
     * @param string $name
     * @param LookupInterface $lookup
     */
    public function registerLookup(string $name, LookupInterface $lookup)
    {
        $this->lookups[$name] = $lookup;
    }

    /**
     * @param EntityRepository $repository
     * @param array|null $array
     * @return QueryBuilder
     */
    public function parse(EntityRepository $repository, array $array): QueryBuilder
    {
        return $this->doParse($repository, $array);
    }

    /**
     * @param EntityRepository $repository
     * @param array $array
     * @return QueryBuilder
     */
    protected function doParse(EntityRepository $repository, array $array): QueryBuilder
    {
        $rootAlias = 'f';
        $qb = $repository->createQueryBuilder($rootAlias);
        $parameters = [];

        $i = 0;
        foreach ($array as $parts) {
            foreach ($parts as $type => $conditions) {
                $comparsions = [];
                $method = sprintf("%sX", strtolower($type));

                foreach ($conditions as $condition) {
                    list($column, $name, $value) = $condition;

                    $lookup = $this->getLookup($name);
                    $parameters[] = $value;

                    if (strpos($column, '__') !== false) {
                        list($relation, $relationColumn) = explode('__', $column);
                        if (array_key_exists($relation, $this->joins)) {
                            $alias = $this->joins[$relation];
                        } else {
                            $this->joins[$relation] = $alias = substr($relation, 0, 1);
                            $qb->leftJoin(
                                sprintf("%s.%s", $rootAlias, $relation),
                                $alias
                            );
                        }

                        $comparsions[] = $lookup->parse($qb, $alias, $i, $relationColumn);
                    } else {
                        $comparsions[] = $lookup->parse($qb, $rootAlias, $i, $column);
                    }

                    $i++;
                }

                $qb->andWhere(call_user_func_array([$qb->expr(), $method], $comparsions));
            }
        }

        return $qb->setParameters($parameters);
    }

    /**
     * @param string $name
     * @return LookupInterface
     */
    protected function getLookup(string $name): LookupInterface
    {
        $lookup = $this->lookups[$name] ?? null;
        if (null === $lookup) {
            throw new \LogicException(sprintf(
                'Unknown lookup: %s', $name
            ));
        }

        return $lookup;
    }

    /**
     * @param array $relations
     */
    public function setAllowedRelations(array $relations)
    {
        $this->relations = $relations;
    }
}