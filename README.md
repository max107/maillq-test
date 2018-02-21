# Пример использования

```php
<?php

$userQuery = [
    // Типичный пример формирования данных практически в любом query builder
    // Пример разработанного мной QueryBuilder https://github.com/MindyPHP/QueryBuilder
    ['and', [
        ['or', [
            // Exact - простое условие, которое генерирует =
            // Подобную логику я использую в продакшене с Mindy ORM
            // своей разработки только lookup'ы в ней сквозные на всю ORM.
            // Об этом ниже
            ['email', 'exact', 'foo@bar.com'],
            ['email', 'exact', 'bar@foo.com']
        ]],
        ['and', [
            // Пример запроса по отношению about
            // lookup'ы разбиваются по __ и на основе левой части строится запрос,
            // в остальном логика аналогичная примеру выше
            ['about__item', 'exact', 'country'],
            ['about__value', 'exact', 'russia']
        ]],
    ]],
];

$lookupBuilder = new \App\LookupBuilder\LookupBuilder();

/** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
$queryBuilder = $lookupBuilder
    ->setEntityRepository($repository)
    ->parse($userQuery);

echo $queryBuilder->getQuery()->getDQL();
// Result: SELECT f FROM App\Entity\User f LEFT JOIN f.about a WHERE (f.email = ?0 OR f.email = ?1) AND (a.item = ?2 AND a.value = ?3)
```

Подробнее в `./tests/LookupBuilderTest.php`.

---

Данная реализация имеет несколько проблем с архитектурой из-за малого времени потраченного на 
рефакторинг, но в целом может быть использована для любой сущности. Может быть доработана до следующего вида:

```php
<?php

$repo->filter([
    'group__permission__code__in' => ['issue.can_delete', 'issue.can_update']
])->getQuery();
```

---

Как это по умолчанию используется в [Mindy Orm](https://github.com/MindyPHP/MindyORM):

```php
User::objects()->filter([
    'about__item' => 'country',
    'about__value__istartswith' => 'russian federation'
])->all();
```

# Как добавить свои lookup?

На примере `!=` мы можем добавить lookup, скажем, `isnot`

Его реализация будет выглядеть следующим образом:

```php
class IsNotExactLookup implements LookupInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(QueryBuilder $builder, string $alias, int $number, string $column)
    {
        return $builder->expr()->not(
            $builder->expr()->eq(
                sprintf('%s.%s', $alias, $column),
                sprintf("?%d", $number)
            )
        );
    }
}
```

Подключение

```php
$builder->registerLookup('exact', new ExactLookup());
$builder->registerLookup('isnot', new IsNotExactLookup());
```

# Lookup

Простейший интерфейс

```php
interface LookupInterface
{
    /**
     * @param QueryBuilder $builder
     * @param string $alias column alias
     * @param int $number value placeholder
     * @param string $column
     * @void
     */
    public function parse(QueryBuilder $builder, string $alias, int $number, string $column);
}
```

# Суммарно затраченное время на реализацию

Суммарно затраченное время на реализацию составляет 2 часа 46 минут. Паралельно мне приходилось отвлекаться 
на выполнение текущей работы.
