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

namespace BaksDev\Avito\UseCase\Admin\NewEdit\Tests;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Entity\Modifier\AvitoTokenModify;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditHandler;
use BaksDev\Avito\UseCase\Admin\NewEdit\Profile\AvitoTokenProfileDTO;
use BaksDev\Core\Type\Modify\Modify\ModifyActionNew;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito
 * @group avito-usecase
 */
#[When(env: 'test')]
class AvitoTokenNewTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $avitoToken = $em->getRepository(AvitoToken::class)
            ->find(UserProfileUid::TEST);

        if ($avitoToken)
        {
            $em->remove($avitoToken);
        }

        $avitoTokenEvent = $em->getRepository(AvitoTokenEvent::class)
            ->findBy(['profile' => UserProfileUid::TEST]);

        foreach ($avitoTokenEvent as $event)
        {
            $em->remove($event);
        }

        $em->flush();
        $em->clear();
    }

    public function testNew(): void
    {
        $newDTO = new AvitoTokenNewEditDTO();

        $newDTO->setProfile(new UserProfileUid(UserProfileUid::TEST));
        self::assertTrue($newDTO->getProfile()->equals(UserProfileUid::TEST));

        $newClient = 'new_client_TEST';
        $newDTO->setClient($newClient);
        self::assertSame($newClient, $newDTO->getClient());

        $newSecret = 'new_secret_TEST';
        $newDTO->setSecret($newSecret);
        self::assertSame($newSecret, $newDTO->getSecret());

        self::assertTrue($newDTO->getActive());
        $newDTO->setActive(false);
        self::assertNotTrue($newDTO->getActive());

        $tokenProfile = new AvitoTokenProfileDTO();
        $tokenProfile->setAddress('new_city_TEST');
        $tokenProfile->setManager('new_manager_TEST');
        $tokenProfile->setPhone('new_phone_TEST');

        self::assertSame(0, $tokenProfile->getPercent());
        $tokenProfile->setPercent(1);
        self::assertSame(1, $tokenProfile->getPercent());

        $newDTO->setTokenProfile($tokenProfile);

        $container = self::getContainer();

        /** @var AvitoTokenNewEditHandler $handler */
        $handler = $container->get(AvitoTokenNewEditHandler::class);
        $newAvitoToken = $handler->handle($newDTO);
        self::assertTrue($newAvitoToken instanceof AvitoToken);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $modifier = $em->getRepository(AvitoTokenModify::class)
            ->find($newAvitoToken->getEvent());

        self::assertTrue($modifier->equals(ModifyActionNew::ACTION));
    }
}
