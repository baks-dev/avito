<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Avito\UseCase\Admin\Tests;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Entity\Modifier\AvitoTokenModify;
use BaksDev\Avito\UseCase\Admin\Delete\AvitoTokenDeleteDTO;
use BaksDev\Avito\UseCase\Admin\Delete\AvitoTokenDeleteHandler;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditHandler;
use BaksDev\Core\Type\Modify\Modify\ModifyActionDelete;
use BaksDev\Core\Type\Modify\Modify\ModifyActionNew;
use BaksDev\Core\Type\Modify\Modify\ModifyActionUpdate;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito
 */
#[When(env: 'test')]
final class AvitoTokenUseCaseTest extends KernelTestCase
{
    private static UserProfileUid $profile;

    public static function setUpBeforeClass(): void
    {
        self::$profile = new UserProfileUid(UserProfileUid::TEST);

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $avitoToken = $em->getRepository(AvitoToken::class)
            ->find(self::$profile);

        if ($avitoToken)
        {
            $em->remove($avitoToken);

            $avitoTokenEvent = $em->getRepository(AvitoTokenEvent::class)
                ->findBy(['profile' => self::$profile]);

            foreach ($avitoTokenEvent as $event)
            {
                $modifier = $em->getRepository(AvitoTokenModify::class)
                    ->findOneBy(['event' => $event->getId()]);

                $em->remove($modifier);
                $em->remove($event);
            }

            $em->flush();
        }

        $em->clear();
    }

    public static function tearDownAfterClass(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $events = $em->getRepository(AvitoTokenEvent::class)
            ->findBy(['profile' => self::$profile]);

        foreach ($events as $event)
        {
            $modifier = $em->getRepository(AvitoTokenModify::class)
                ->findOneBy(['event' => $event->getId()]);

            $em->remove($modifier);
            $em->remove($event);
        }

        $em->flush();
        $em->clear();
    }

    public function testNew(): AvitoToken
    {
        $newDTO = new AvitoTokenNewEditDTO();

        $newDTO->setProfile(self::$profile);
        self::assertSame(self::$profile, $newDTO->getProfile());

        $newClient = 'new_client';
        $newDTO->setClient($newClient);
        self::assertSame($newClient, $newDTO->getClient());

        $newSecret = 'new_secret';
        $newDTO->setSecret($newSecret);
        self::assertSame($newSecret, $newDTO->getSecret());

        $newDTO->setActive(false);
        self::assertNotTrue($newDTO->getActive());

        $newDTO->setPercent(100);
        self::assertSame(100, $newDTO->getPercent());

        $container = self::getContainer();

        /** @var AvitoTokenNewEditHandler $handler */
        $handler = $container->get(AvitoTokenNewEditHandler::class);
        $newAvitoToken = $handler->handle($newDTO);
        self::assertTrue(($newAvitoToken instanceof AvitoToken));

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $modifier = $em->getRepository(AvitoTokenModify::class)
            ->find($newAvitoToken->getEvent());

        self::assertTrue($modifier->equals(ModifyActionNew::ACTION));

        return $newAvitoToken;
    }

    /**
     * @depends testNew
     */
    public function testEdit(AvitoToken $newAvitoToken): AvitoToken
    {
        $editDTO = new AvitoTokenNewEditDTO();

        $editDTO->setId($newAvitoToken->getEvent());
        self::assertSame($newAvitoToken->getEvent(), $editDTO->getEvent());

        $editDTO->setProfile($newAvitoToken->getId());
        self::assertSame($newAvitoToken->getId(), $editDTO->getProfile());
        self::assertSame(self::$profile, $editDTO->getProfile());

        $editClient = 'edit_client';
        $editDTO->setClient($editClient);
        self::assertSame($editClient, $editDTO->getClient());

        $editSecret = 'edit_secret';
        $editDTO->setSecret($editSecret);
        self::assertSame($editSecret, $editDTO->getSecret());

        $editDTO->setActive(true);
        self::assertTrue($editDTO->getActive());

        $editDTO->setPercent(0);
        self::assertSame(0, $editDTO->getPercent());

        $container = self::getContainer();

        /** @var AvitoTokenNewEditHandler $handler */
        $handler = $container->get(AvitoTokenNewEditHandler::class);
        $editAvitoToken = $handler->handle($editDTO);
        self::assertTrue(($editAvitoToken instanceof AvitoToken));

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $modifier = $em->getRepository(AvitoTokenModify::class)
            ->find($editAvitoToken->getEvent());

        self::assertTrue($modifier->equals(ModifyActionUpdate::ACTION));

        return $editAvitoToken;
    }

    /**
     * @depends testEdit
     */
    public function testDelete(AvitoToken $editAvitoToken): void
    {
        $deleteDTO = new AvitoTokenDeleteDTO();

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $event = $em->getRepository(AvitoTokenEvent::class)
            ->find($editAvitoToken->getEvent());

        $event->getDto($deleteDTO);

        /** @var AvitoTokenDeleteHandler $handler */
        $handler = $container->get(AvitoTokenDeleteHandler::class);
        $deleteAvitoToken = $handler->handle($deleteDTO);
        self::assertTrue(($deleteAvitoToken instanceof AvitoToken));

        $modifier = $em->getRepository(AvitoTokenModify::class)
            ->find($deleteAvitoToken->getEvent());

        // @todo условие не выполняется, так как в корне нет информации о событии удаления
        // self::assertTrue($modifier->equals(ModifyActionDelete::ACTION));

        $avitoToken = $em->getRepository(AvitoToken::class)
            ->find($deleteAvitoToken->getId());
        self::assertNull($avitoToken);
    }
}
