<?php

declare(strict_types=1);

namespace BaksDev\Avito\Repository\AvitoAuthorizationByProfile;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Avito\Type\Authorization\AvitoTokenAuthorization;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;

final class AvitoAuthorizationByProfileRepository implements AvitoAuthorizationByProfileInterface
{
    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder
    ) {}

    public function getAuthorization(UserProfileUid $profile): ?AvitoTokenAuthorization
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(AvitoToken::class, 'avito_token')
            ->where('avito_token.id = :profile')
            ->setParameter('profile', $profile, UserProfileUid::TYPE);

        $qb->join(
            'avito_token',
            AvitoTokenEvent::class,
            'event',
            'event.id = avito_token.event AND event.active = true',
        );

        $qb->join(
            'avito_token',
            UserProfileInfo::class,
            'info',
            'info.profile = avito_token.id AND info.status = :status',
        );

        $qb->setParameter('status', new UserProfileStatus(UserProfileStatusActive::class), UserProfileStatus::TYPE);

        $qb->select('avito_token.id AS profile');
        $qb->addSelect('event.client AS client');
        $qb->addSelect('event.secret AS secret');

        /* Кешируем результат ORM */
        return $qb
            ->enableCache('avito', 86400)
            ->fetchHydrate(AvitoTokenAuthorization::class);
    }
}
