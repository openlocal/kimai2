<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Tests;

use App\DataFixtures\UserFixtures;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\HttpKernelBrowser;

/**
 * A trait to be used in all tests that extend the KernelTestCase.
 */
trait KernelTestTrait
{
    public function getEntityManager(): EntityManagerInterface
    {
        if (!$this instanceof KernelTestCase) {
            throw new \Exception('KernelTestTrait can only be used in a KernelTestCase');
        }

        return $this::$container->get('doctrine.orm.entity_manager');
    }

    /**
     * @param HttpKernelBrowser|EntityManager|KernelTestCase $client
     * @param Fixture $fixture
     */
    protected function importFixture($client, Fixture $fixture)
    {
        if ($client instanceof EntityManager) {
            $em = $client;
        } elseif ($this instanceof KernelTestCase) {
            $em = $this::$container->get('doctrine.orm.entity_manager');
        } else {
            throw new \InvalidArgumentException('Need an EntityManager to import fixtures');
        }

        $loader = new Loader();
        $loader->addFixture($fixture);

        $executor = new ORMExecutor($em, null);
        $executor->execute($loader->getFixtures(), true);
    }

    protected function getUserByName(string $username): ?User
    {
        return $this->getEntityManager()->getRepository(User::class)->findOneBy(['username' => $username]);
    }

    /**
     * @param EntityManager $em
     * @param string $role
     * @return User|null
     */
    protected function getUserByRole(EntityManager $em, string $role = User::ROLE_USER)
    {
        $name = null;

        switch ($role) {
            case User::ROLE_SUPER_ADMIN:
                $name = UserFixtures::USERNAME_SUPER_ADMIN;
                break;

            case User::ROLE_ADMIN:
                $name = UserFixtures::USERNAME_ADMIN;
                break;

            case User::ROLE_TEAMLEAD:
                $name = UserFixtures::USERNAME_TEAMLEAD;
                break;

            case User::ROLE_USER:
                $name = UserFixtures::USERNAME_USER;
                break;

            default:
                return null;
        }

        return $this->getUserByName($name);
    }
}
