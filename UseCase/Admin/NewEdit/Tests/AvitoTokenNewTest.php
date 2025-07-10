<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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
 *
 */

namespace BaksDev\Avito\UseCase\Admin\NewEdit\Tests;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Entity\Modifier\AvitoTokenModify;
use BaksDev\Avito\UseCase\Admin\NewEdit\Active\AvitoTokenActiveDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Address\AvitoTokenAddressDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditHandler;
use BaksDev\Avito\UseCase\Admin\NewEdit\Client\AvitoTokenClientDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Manager\AvitoTokenManagerDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Percent\AvitoTokenPercentDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Phone\AvitoTokenPhoneDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Secret\AvitoTokenSecretDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\User\AvitoTokenUserDTO;
use BaksDev\Core\Type\Modify\Modify\ModifyActionNew;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Tests\UserNewUserProfileHandleTest;
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
    private static UserProfile|null $profile;

    public static function setUpBeforeClass(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        /** Проверка существования тестового профиля */
        $userProfileTest = $em->getRepository(UserProfile::class)
            ->findOneBy(['id' => UserProfileUid::TEST]);

        self::$profile = $userProfileTest;

        $avitoToken = $em->getRepository(AvitoToken::class)
            ->find(UserProfileUid::TEST);

        if($avitoToken)
        {
            $em->remove($avitoToken);
        }

        $avitoTokenEvent = $em->getRepository(AvitoTokenEvent::class)
            ->findBy(['profile' => UserProfileUid::TEST]);

        foreach($avitoTokenEvent as $event)
        {
            $em->remove($event);
        }

        $em->flush();
        $em->clear();
    }

    public function testNew(): void
    {
        self::assertInstanceOf(UserProfile::class, self::$profile, 'Создайте тестовый профиль с помощью '.UserNewUserProfileHandleTest::class);
        self::assertTrue(self::$profile->getId()->equals(UserProfileUid::TEST));

        $avitoTokenNewEditDTO = new AvitoTokenNewEditDTO();

        /** Profile */
        $avitoTokenNewEditDTO->setProfile(new UserProfileUid(UserProfileUid::TEST));
        self::assertTrue($avitoTokenNewEditDTO->getProfile()->equals(UserProfileUid::TEST));

        ////

        // AvitoTokenActiveDTO
        $avitoTokenActiveDTO = new AvitoTokenActiveDTO();
        $avitoTokenActiveDTO->setValue(true);
        self::assertSame(true, $avitoTokenActiveDTO->getValue());

        $avitoTokenNewEditDTO->setActive($avitoTokenActiveDTO);

        // AvitoTokenClientDTO
        $avitoTokenClientDTO = new AvitoTokenClientDTO();
        $avitoTokenClientDTO->setValue('AvitoTokenClientDTO');
        self::assertSame('AvitoTokenClientDTO', $avitoTokenClientDTO->getValue());

        $avitoTokenNewEditDTO->setClient($avitoTokenClientDTO);

        // AvitoTokenSecretDTO
        $avitoTokenSecretDTO = new AvitoTokenSecretDTO();
        $avitoTokenSecretDTO->setValue('AvitoTokenSecretDTO');
        self::assertSame('AvitoTokenSecretDTO', $avitoTokenSecretDTO->getValue());

        $avitoTokenNewEditDTO->setSecret($avitoTokenSecretDTO);

        // AvitoTokenManagerDTO
        $avitoTokenManagerDTO = new AvitoTokenManagerDTO();
        $avitoTokenManagerDTO->setValue('AvitoTokenManagerDTO');
        self::assertSame('AvitoTokenManagerDTO', $avitoTokenManagerDTO->getValue());

        $avitoTokenNewEditDTO->setManager($avitoTokenManagerDTO);

        // AvitoTokenPercentDTO
        $avitoTokenPercentDTO = new AvitoTokenPercentDTO();
        $avitoTokenPercentDTO->setValue('AvitoTokenPercentDTO');
        self::assertSame('AvitoTokenPercentDTO', $avitoTokenPercentDTO->getValue());

        $avitoTokenNewEditDTO->setPercent($avitoTokenPercentDTO);

        // AvitoTokenAddressDTO
        $avitoTokenAddressDTO = new AvitoTokenAddressDTO();
        $avitoTokenAddressDTO->setValue('AvitoTokenAddressDTO');
        self::assertSame('AvitoTokenAddressDTO', $avitoTokenAddressDTO->getValue());

        $avitoTokenNewEditDTO->setAddress($avitoTokenAddressDTO);

        // AvitoTokenPhoneDTO
        $avitoTokenPhoneDTO = new AvitoTokenPhoneDTO();
        $avitoTokenPhoneDTO->setValue('AvitoTokenPhoneDTO');
        self::assertSame('AvitoTokenPhoneDTO', $avitoTokenPhoneDTO->getValue());

        $avitoTokenNewEditDTO->setPhone($avitoTokenPhoneDTO);

        // AvitoTokenUserDTO
        $avitoTokenUserDTO = new AvitoTokenUserDTO();
        $avitoTokenUserDTO->setValue('AvitoTokenUserDTO');
        self::assertSame('AvitoTokenUserDTO', $avitoTokenUserDTO->getValue());

        $avitoTokenNewEditDTO->setUser($avitoTokenUserDTO);

        ////

        /** @var AvitoTokenNewEditHandler $handler */
        $handler = self::getContainer()->get(AvitoTokenNewEditHandler::class);
        $newAvitoToken = $handler->handle($avitoTokenNewEditDTO);
        self::assertTrue($newAvitoToken instanceof AvitoToken);

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        /** Проверка соответствия модификатора */
        $modifier = $em->getRepository(AvitoTokenModify::class)
            ->find($newAvitoToken->getEvent());

        self::assertTrue($modifier->equals(ModifyActionNew::ACTION));
    }
}
