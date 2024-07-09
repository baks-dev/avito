<?php

namespace BaksDev\Avito\UseCase\Admin\Tests;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Entity\Modifier\AvitoTokenModify;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditHandler;
use BaksDev\Core\Type\Modify\Modify\ModifyActionUpdate;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\AssertionFailedError;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito
 * @group avito-usecase
 *
 * @depends BaksDev\Avito\UseCase\Admin\Tests\AvitoTokenNewTest::class
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
            ->innerJoin(AvitoToken::class, 'avito_token', 'WITH', 'avito_token.event = avito_token_event.id')
            ->where('avito_token.id = :id')
            ->setParameter('id', UserProfileUid::TEST, UserProfileUid::TYPE)
            ->getQuery()
            ->getOneOrNullResult();

        self::assertNotNull($activeEvent);

        $editDTO = new AvitoTokenNewEditDTO();
        // гидрируем DTO
        $activeEvent->getDto($editDTO);

        self::assertSame('new_test_client', $editDTO->getClient());
        $editDTO->setClient('edit_test_client');

        self::assertSame('new_test_secret', $editDTO->getSecret());
        $editDTO->setSecret('edit_test_secret');

        self::assertFalse(false, $editDTO->getActive());
        $editDTO->setActive(true);

        self::assertSame(100, $editDTO->getPercent());
        $editDTO->setPercent(0);

        /** @var AvitoTokenNewEditHandler $handler */
        $handler = $container->get(AvitoTokenNewEditHandler::class);
        $editAvitoToken = $handler->handle($editDTO);
        self::assertTrue($editAvitoToken instanceof AvitoToken);

        $modifier = $em->getRepository(AvitoTokenModify::class)
            ->find($editAvitoToken->getEvent());

        self::assertTrue($modifier->equals(ModifyActionUpdate::ACTION));
    }
}
