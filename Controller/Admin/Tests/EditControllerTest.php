<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Avito\Controller\Admin\Tests;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Type\Event\AvitoTokenEventUid;
use BaksDev\Users\User\Tests\TestUserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito
 * @group avito-controller
 */
#[When(env: 'test')]
final class EditControllerTest extends WebTestCase
{
    private const string URL = '/admin/avito/token/edit/%s';

    private const string ROLE = 'ROLE_AVITO_TOKEN_EDIT';

    private static ?AvitoTokenEventUid $eventId = null;

    public static function setUpBeforeClass(): void
    {
        // Получаем одно из событий
        $em = self::getContainer()->get(EntityManagerInterface::class);
        self::$eventId = $em->getRepository(AvitoToken::class)->findOneBy([], ['id' => 'DESC'])?->getEvent();

        $em->clear();
        //$em->close();
    }

    /** Доступ по роли */
    public function testRoleSuccessful(): void
    {
        // Получаем одно из событий
        $eventId = self::$eventId;

        if ($eventId)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach (TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $usr = TestUserAccount::getModer(self::ROLE);

                $client->loginUser($usr, 'user');
                $client->request('GET', sprintf(self::URL, $eventId->getValue()));

                self::assertResponseIsSuccessful();
            }
        }

        // @todo зачем данная проверка присутствует в каждом тесте?
        self::assertTrue(true);
    }

    /** Доступ по роли ROLE_ADMIN */
    public function testRoleAdminSuccessful(): void
    {
        // Получаем одно из событий
        $eventId = self::$eventId;

        if ($eventId)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach (TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);
                $usr = TestUserAccount::getAdmin();

                $client->loginUser($usr, 'user');
                $client->request('GET', sprintf(self::URL, $eventId->getValue()));

                self::assertResponseIsSuccessful();
            }
        }

        self::assertTrue(true);
    }

    /** Доступ по роли ROLE_USER */
    public function testRoleUserDeny(): void
    {
        // Получаем одно из событий
        $eventId = self::$eventId;

        if ($eventId)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach (TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $usr = TestUserAccount::getUsr();
                $client->loginUser($usr, 'user');
                $client->request('GET', sprintf(self::URL, $eventId->getValue()));

                self::assertResponseStatusCodeSame(403);
            }
        }

        self::assertTrue(true);
    }

    /** Доступ без роли */
    public function testGuestFiled(): void
    {
        // Получаем одно из событий
        $eventId = self::$eventId;

        if ($eventId)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach (TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $client->request('GET', sprintf(self::URL, $eventId->getValue()));

                // Full authentication is required to access this resource
                self::assertResponseStatusCodeSame(401);
            }
        }

        self::assertTrue(true);
    }
}
