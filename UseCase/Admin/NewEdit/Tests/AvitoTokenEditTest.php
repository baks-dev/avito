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
 */

namespace BaksDev\Avito\UseCase\Admin\NewEdit\Tests;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Entity\Modifier\AvitoTokenModify;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditHandler;
use BaksDev\Core\Type\Modify\Modify\ModifyActionUpdate;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('avito')]

class AvitoTokenEditTest extends KernelTestCase
{
    #[DependsOnClass(AvitoTokenNewTest::class)]
    public function testEdit(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** Находим токен по тестовому идентификатору профиля */
        $token = $em
            ->getRepository(AvitoToken::class)
            ->find(UserProfileUid::TEST);

        self::assertNotNull($token);

        /** Находим активное событие */
        $activeEvent = $em
            ->getRepository(AvitoTokenEvent::class)
            ->find($token->getEvent());

        self::assertNotNull($activeEvent);

        $avitoTokenNewEditDTO = new AvitoTokenNewEditDTO();

        $activeEvent->getDto($avitoTokenNewEditDTO);

        /** @var AvitoTokenNewEditHandler $handler */
        $handler = $container->get(AvitoTokenNewEditHandler::class);
        $editAvitoToken = $handler->handle($avitoTokenNewEditDTO);
        self::assertTrue($editAvitoToken instanceof AvitoToken);

        $modifier = $em->getRepository(AvitoTokenModify::class)
            ->find($editAvitoToken->getEvent());

        self::assertTrue($modifier->equals(ModifyActionUpdate::ACTION));
    }
}
