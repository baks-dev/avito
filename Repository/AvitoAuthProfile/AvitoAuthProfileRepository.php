<?php

declare(strict_types=1);

namespace BaksDev\Avito\Repository\AvitoAuthProfile;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Entity\Profile\AvitoTokenProfile;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;

final class AvitoAuthProfileRepository implements AvitoAuthProfileInterface
{
    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder
    ) {}

    public function findByProfile(UserProfileUid $profile): ?array
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(AvitoToken::class, 'avito_token')
            ->where('avito_token.id = :profile')
            ->setParameter('profile', $profile, UserProfileUid::TYPE);

        $dbal->join(
            'avito_token',
            AvitoTokenEvent::class,
            'avito_token_event',
            'avito_token_event.id = avito_token.event AND avito_token_event.active = true',
        );

        $dbal->join(
            'avito_token_event',
            AvitoTokenProfile::class,
            'avito_token_profile',
            'avito_token_profile.event = avito_token_event.id',
        );

        $dbal->join(
            'avito_token',
            UserProfileInfo::class,
            'info',
            'info.profile = avito_token.id AND info.status = :status',
        );

        $dbal->setParameter('status', new UserProfileStatus(UserProfileStatusActive::class), UserProfileStatus::TYPE);

        $dbal->select('avito_token_profile.address AS avito_token_address');
        $dbal->addSelect('avito_token_profile.phone AS avito_token_phone');
        $dbal->addSelect('avito_token_profile.manager AS avito_token_manager');
        $dbal->addSelect('avito_token_profile.percent AS avito_token_percent');

        /* Кешируем результат ORM */
        return $dbal
//            ->enableCache('avito-board', 86400)
            ->fetchAllAssociative();
    }
}
