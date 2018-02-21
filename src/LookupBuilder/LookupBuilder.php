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
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\SchemaTool;

class LookupBuilder
{
    private const alias = 'f';

    /**
     * @var array
     */
    protected $lookups = [];
    /**
     * @var array
     */
    private $joins = [];

    /**
     * @var int
     */
    private $i = 0;

    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var QueryBuilder
     */
    private $qb;

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
     * @return $this
     */
    public function setEntityRepository(EntityRepository $repository)
    {
        $this->repository = $repository;
        $this->qb = $repository->createQueryBuilder(self::alias);
        return $this;
    }

    /**
     * @param array|null $array
     * @return QueryBuilder
     */
    public function parse(array $array): QueryBuilder
    {
        return $this->doParse($array);
    }

    /**
     * @param array $parts
     * @return QueryBuilder
     */
    protected function doParse(array $parts): QueryBuilder
    {
        $criteria = $this->parseConditions($parts);

        $this->qb->where($this->exprFromArray('and', $criteria));
        $this->qb->setParameters($this->parameters);

        return $this->qb;
    }

    protected function buildJoin($tableAlias, $relation): string
    {
        if (array_key_exists($relation, $this->joins)) {
            $alias = $this->joins[$relation];
        } else {
            $this->joins[$relation] = $alias = substr($relation, 0, 1);
            $this->qb->leftJoin(
                sprintf("%s.%s", $tableAlias, $relation),
                $alias
            );
        }

        return $alias;
    }

    /**
     * @param $condition
     * @return Comparison
     */
    protected function parseLookup(array $condition)
    {
        list($column, $name, $value) = $condition;

        $comparison = $this->parseCondition($column, $this->getLookup($name));
        $this->addParameter($value);

        return $comparison;
    }

    /**
     * @param $value
     * @return $this
     */
    protected function addParameter($value)
    {
        $this->parameters[] = $value;
        $this->i++;

        return $this;
    }

    /**
     * @param $column
     * @param LookupInterface $lookup
     * @return mixed
     */
    protected function parseCondition($column, LookupInterface $lookup)
    {
        if (strpos($column, '__') !== false) {
            list($relation, $relationColumn) = explode('__', $column);
            $alias = $this->buildJoin(self::alias, $relation);

            return $lookup->parse($this->qb, $alias, $this->i, $relationColumn);
        } else {
            return $lookup->parse($this->qb, self::alias, $this->i, $column);
        }
    }

    protected function exprFromArray(string $type, array $expressions)
    {
        return call_user_func_array(
            [$this->qb->expr(), sprintf("%sX", strtolower($type))],
            $expressions
        );
    }

    protected function parseConditions(array $conditions)
    {
        $parts = [];
        foreach ($conditions as $condition) {
            if (in_array(current($condition), ['and', 'or'])) {
                list($type, $children) = $condition;

                $parts[] = $this->exprFromArray($type, $this->parseConditions($children));
            } else {
                $parts[] = $this->parseLookup($condition);
            }
        }

        return $parts;
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
}