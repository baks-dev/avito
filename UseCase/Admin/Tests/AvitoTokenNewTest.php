<?php

namespace BaksDev\Avito\UseCase\Admin\Tests;

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

    public function testNew(): void
    {
        $newDTO = new AvitoTokenNewEditDTO();

        $newDTO->setProfile(self::$profile);
        self::assertSame(self::$profile, $newDTO->getProfile());

        $newClient = 'new_test_client';
        $newDTO->setClient($newClient);
        self::assertSame($newClient, $newDTO->getClient());

        $newSecret = 'new_test_secret';
        $newDTO->setSecret($newSecret);
        self::assertSame($newSecret, $newDTO->getSecret());

        $newDTO->setActive(false);
        self::assertNotTrue($newDTO->getActive());

        $tokenProfile = new AvitoTokenProfileDTO();
        $tokenProfile->setAddress('Москва');
        $tokenProfile->setManager('Шестопалов А.П.');
        $tokenProfile->setPhone('+7987654321');

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
