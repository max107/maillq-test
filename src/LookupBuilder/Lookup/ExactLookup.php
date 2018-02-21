<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\LookupBuilder\Lookup;

use Doctrine\ORM\QueryBuilder;

class ExactLookup implements LookupInterface
{
    public function parse(QueryBuilder $builder, string $alias, int $number, string $key)
    {
        return $builder->expr()->eq(
            sprintf('%s.%s', $alias, $key),
            sprintf("?%d", $number)
        );
    }
}