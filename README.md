[![Build Status](https://travis-ci.org/max107/test1.svg?branch=master)](https://travis-ci.org/max107/test1)

# Задача

```
Необходимо разработать универсальный класс поиска пользователей. На входе передаются фильтры поиска в определенном вами формате. На выходе возвращается массив данных найденных пользователей с данными "users.id", "users.email", "users.role", "users.reg_date". Должна быть возможность комбинировать фильтры в логические конструкции И/ИЛИ. Для каждого фильтра необходимо добавить возможность указывать соответствие (=) или несоответствие (!=) целевому значению. Вложенность логических конструкций должна быть бесконечна. Должна быть возможность расширения списка фильтров без изменения основного алгоритма поиска. 

Должна быть возможность комбинирования любых фильтров с любыми логическими операторами.

Имеется две таблицы с данными (структура таблиц представлена ниже):
users - список пользователей
users_about - данные пользователей

Необходимо реализовать фильтры:
ID (users.id)
E-Mail (users.email)
Страна (users_about.item = "country")
Имя (users_about.item = "firstname")
Состояние пользователя (users_about.item = "state")

Примеры

Должна быть возможность составлять такие условия поиска как:
- ((ID = 1000) ИЛИ (Страна != Россия))
- ((Страна = Россия) И (Состояние пользователя != active) И (Граватар = Нет))
- ((((Страна != Россия) ИЛИ (Состояние пользователя = active)) И (E-Mail = user@domain.com)) ИЛИ (Имя != ""))

Выполнить эту задачу необходимо на Symfony 4. Все данные должны находиться в БД MySQL. Необходимо предоставить описание формата передачи фильтров в поиск и реализация фильтров из примера. 
```

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

Как это по умолчанию используется в [Mindy Orm](https://github.com/MindyPHP/Orm):

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
