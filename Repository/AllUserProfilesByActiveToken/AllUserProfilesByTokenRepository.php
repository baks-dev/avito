<?php

declare(strict_types=1);

namespace BaksDev\Avito\Repository\AllUserProfilesByActiveToken;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Repository\AllAvitoToken\AllAvitoTokenInterface;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;

final class AllUserProfilesByTokenRepository implements AllUserProfilesByActiveTokenInterface
{
    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    public function findProfilesByActiveToken(): \Generator
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->from(AvitoToken::class, 'avito_token');

        $dbal
            ->join(
                'avito_token',
                AvitoTokenEvent::class,
                'avito_token_event',
                '
                    avito_token_event.profile = avito_token.id AND 
                    avito_token_event.id = avito_token.event AND 
                    avito_token_event.active IS TRUE',
            );

        //        /** Информация о профиле */
        //        $dbal
        //            ->leftJoin(
        //                'avito_token',
        //                UserProfile::class,
        //                'users_profile',
        //                'users_profile.id = avito_token.id'
        //            );
        //
        //        $dbal->join(
        //            'users_profile',
        //            UserProfileEvent::class,
        //            'users_profile_event',
        //            'users_profile_event.id = users_profile.event'
        //        );
        //
        //        $dbal
        //            ->join(
        //                'users_profile',
        //                UserProfileInfo::class,
        //                'users_profile_info',
        //                "
        //                    users_profile_info.profile = avito_token.id AND
        //                    users_profile_info.status = :users_profile_status"
        //            );
        //
        //        $dbal->setParameter('users_profile_status', new UserProfileStatus(UserProfileStatusActive::class), UserProfileStatus::TYPE);
        //
        //        /** Аккаунт */
        //        $dbal->leftJoin(
        //            'users_profile_info',
        //            Account::class,
        //            'account',
        //            'account.id = users_profile_info.usr'
        //        );
        //
        //        $dbal->join(
        //            'account',
        //            AccountEvent::class,
        //            'account_event',
        //            '
        //                account_event.account = account.id AND
        //                account_event.id = account.event'
        //        );
        //
        //        $dbal->join(
        //            'account_event',
        //            AccountStatus::class,
        //            'account_status',
        //            "
        //                account_status.event = account_event.id AND
        //                account_status.status = 'act'"
        //        );

        $dbal->select('avito_token.id as value');

        return $dbal->fetchAllHydrate(UserProfileUid::class);
    }
}
