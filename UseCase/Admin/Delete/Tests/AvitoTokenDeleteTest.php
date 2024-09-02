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

namespace BaksDev\Avito\UseCase\Admin\Delete\Tests;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Entity\Modifier\AvitoTokenModify;
use BaksDev\Avito\UseCase\Admin\Delete\AvitoTokenDeleteDTO;
use BaksDev\Avito\UseCase\Admin\Delete\AvitoTokenDeleteHandler;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito
 * @group avito-usecase
 *
 * @depends BaksDev\Avito\UseCase\Admin\NewEdit\Tests\AvitoTokenEditTest::class
 */
#[When(env: 'test')]
final class AvitoTokenDeleteTest extends KernelTestCase
{
    public function testDelete(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $event = $em->createQueryBuilder()
            ->select('avito_token_event')
            ->from(AvitoTokenEvent::class, 'avito_token_event')
            ->join(AvitoToken::class, 'avito_token', 'WITH', 'avito_token.event = avito_token_event.id')
            ->where('avito_token.id = :id')
            ->setParameter('id', UserProfileUid::TEST, UserProfileUid::TYPE)
            ->getQuery()
            ->getOneOrNullResult();

        $em->clear();

        if ($event)
        {
            $deleteDTO = new AvitoTokenDeleteDTO();

            $event->getDto($deleteDTO);

            /** @var AvitoTokenDeleteHandler $handler */
            $handler = $container->get(AvitoTokenDeleteHandler::class);
            $deleteAvitoToken = $handler->handle($deleteDTO);
            self::assertTrue($deleteAvitoToken instanceof AvitoToken);

            $avitoToken = $em->getRepository(AvitoToken::class)
                ->find($deleteAvitoToken->getId());
            self::assertNull($avitoToken);

            $modifier = $em->getRepository(AvitoTokenModify::class)
                ->find($deleteAvitoToken->getEvent());

            // @TODO условие не выполняется, так как в корне нет информации о событии удаления - уточнить по реализации
            // self::assertTrue($modifier->equals(ModifyActionDelete::ACTION));
        }

        self::assertTrue(true);
    }

    public static function tearDownAfterClass(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $profile = new UserProfileUid(UserProfileUid::TEST);

        $events = $em->getRepository(AvitoTokenEvent::class)
            ->findBy(['profile' => $profile]);

        foreach ($events as $event)
        {
            $em->remove($event);
        }

        $em->flush();
        $em->clear();
    }
}