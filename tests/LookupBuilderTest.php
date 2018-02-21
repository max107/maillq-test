<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2018 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests;

use App\Entity\User;
use App\Entity\UserAbout;
use App\LookupBuilder\Lookup\ExactLookup;
use App\LookupBuilder\LookupBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LookupBuilderTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    /**
     * @var LookupBuilder
     */
    private $lb;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $user1 = new User();
        $user1->setEmail('foo@bar.com');
        $this->em->persist($user1);

        $user2 = new User();
        $user2->setEmail('bar@foo.com');
        $this->em->persist($user2);

        $userAbout1 = new UserAbout();
        $userAbout1->setUser($user1);
        $userAbout1->setItem('country');
        $userAbout1->setValue('russia');
        $this->em->persist($userAbout1);

        $userAbout2 = new UserAbout();
        $userAbout2->setUser($user2);
        $userAbout2->setItem('country');
        $userAbout2->setValue('таджикистан епта');
        $this->em->persist($userAbout2);

        $this->em->flush();

        $this->lb = new LookupBuilder();
        $this->lb->registerLookup('exact', new ExactLookup());
        $this->lb->setAllowedRelations(['about']);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }

    public function lookupProvider(): array
    {
        return [
            [
                [
                    ['and' => [['email', 'exact', 'foo@bar.com']]]
                ],
                'SELECT f FROM App\Entity\User f WHERE f.email = ?0',
                1
            ],
            [
                [
                    ['or' => [['email', 'exact', 'foo@bar.com'], ['email', 'exact', 'bar@foo.com']]]
                ],
                'SELECT f FROM App\Entity\User f WHERE f.email = ?0 OR f.email = ?1',
                2
            ],
            [
                [
                    ['and' => [['id', 'exact', 1]]],
                    ['or' => [['email', 'exact', 'foo@bar.com'], ['email', 'exact', 'bar@foo.com']]]
                ],
                'SELECT f FROM App\Entity\User f WHERE f.id = ?0 AND (f.email = ?1 OR f.email = ?2)',
                1
            ],
            [
                [
                    [
                        'and' => [
                            ['about__item', 'exact', 'country'],
                            ['about__value', 'exact', 'russia']
                        ],
                    ],
                ],
                'SELECT f FROM App\Entity\User f LEFT JOIN f.about a WHERE a.item = ?0 AND a.value = ?1',
                1
            ],
            [
                [
                    [
                        'or' => [
                            ['email', 'exact', 'foo@bar.com'],
                            ['email', 'exact', 'bar@foo.com']
                        ]
                    ],
                    [
                        'and' => [
                            ['about__item', 'exact', 'country'],
                            ['about__value', 'exact', 'russia']
                        ],
                    ],
                ],
                'SELECT f FROM App\Entity\User f LEFT JOIN f.about a WHERE a.item = ?0 AND a.value = ?1',
                1
            ],
        ];
    }

    /**
     * @dataProvider lookupProvider
     * @param array $userConditions
     * @param string $expectedDql
     * @param int $expectedCount
     */
    public function testLookupBuilder(array $userConditions, string $expectedDql, int $expectedCount)
    {
        $repository = $this->em->getRepository(User::class);
        $qb = $this->lb->parse($repository, $userConditions);
        $query = $qb->getQuery();
        $this->assertSame($expectedDql, $query->getDQL());
        $this->assertCount($expectedCount, $query->getResult(AbstractQuery::HYDRATE_ARRAY));
    }
}