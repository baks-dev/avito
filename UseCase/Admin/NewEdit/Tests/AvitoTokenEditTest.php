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
use BaksDev\Core\Type\Modify\Modify\ModifyActionUpdate;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito
 * @group avito-usecase
 *
 * @depends BaksDev\Avito\UseCase\Admin\NewEdit\Tests\AvitoTokenNewTest::class
 */

#[When(env: 'test')]
class AvitoTokenEditTest extends KernelTestCase
{
    public function testEdit(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var AvitoTokenEvent|null $activeEvent */
        $activeEvent = $em->createQueryBuilder()
            ->select('avito_token_event')
            ->from(AvitoTokenEvent::class, 'avito_token_event')
            ->join(AvitoToken::class, 'avito_token', 'WITH', 'avito_token.event = avito_token_event.id')
            ->where('avito_token.id = :id')
            ->setParameter('id', UserProfileUid::TEST, UserProfileUid::TYPE)
            ->getQuery()
            ->getOneOrNullResult();


        self::assertNotNull($activeEvent);

        $editDTO = new AvitoTokenNewEditDTO();

        $activeEvent->getDto($editDTO);

        self::assertSame('new_client_TEST', $editDTO->getClient());
        $editDTO->setClient('edit_client_TEST');

        self::assertSame('new_secret_TEST', $editDTO->getSecret());
        $editDTO->setSecret('edit_secret_TEST');

        self::assertFalse(false, $editDTO->getActive());
        $editDTO->setActive(true);

        $tokenProfile = new AvitoTokenProfileDTO();
        $tokenProfile->setAddress('edit_city_TEST');
        $tokenProfile->setManager('edit_manager_TEST');
        $tokenProfile->setPhone('edit_phone_TEST');
        $tokenProfile->setPercent(0);

        $editDTO->setTokenProfile($tokenProfile);

        /** @var AvitoTokenNewEditHandler $handler */
        $handler = $container->get(AvitoTokenNewEditHandler::class);
        $editAvitoToken = $handler->handle($editDTO);
        self::assertTrue($editAvitoToken instanceof AvitoToken);

        $modifier = $em->getRepository(AvitoTokenModify::class)
            ->find($editAvitoToken->getEvent());

        self::assertTrue($modifier->equals(ModifyActionUpdate::ACTION));
    }
}
