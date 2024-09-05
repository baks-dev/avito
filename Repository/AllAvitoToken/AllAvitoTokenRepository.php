<?php

declare(strict_types=1);

namespace BaksDev\Avito\Repository\AllAvitoToken;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class AllAvitoTokenRepository implements AllAvitoTokenInterface
{
    private ?SearchDTO $search = null;

    private ?UserProfileUid $profile = null;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
    ) {}

    public function profile(UserProfileUid|string $profile): self
    {
        if (is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;

        return $this;
    }

    public function search(SearchDTO $search): self
    {
        $this->search = $search;

        return $this;
    }

    public function findAll(): PaginatorInterface
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->select('avito_token.id');
        $dbal->addSelect('avito_token.event');
        $dbal->from(AvitoToken::class, 'avito_token');

        /** Eсли не админ - только токен профиля */
        if ($this->profile)
        {
            $dbal->where('avito_token.id = :profile')
                ->setParameter('profile', $this->profile, UserProfileUid::TYPE);
        }

        $dbal
            ->addSelect('event.active')
            ->join(
                'avito_token',
                AvitoTokenEvent::class,
                'event',
                "
                    event.profile = avito_token.id AND
                    event.id = avito_token.event"
            );

        // ОТВЕТСТВЕННЫЙ

        // UserProfile
        $dbal
            ->addSelect('users_profile.id as users_profile_id')
            ->addSelect('users_profile.event as users_profile_event')
            ->leftJoin(
                'avito_token',
                UserProfile::class,
                'users_profile',
                'users_profile.id = avito_token.id'
            );

        // Info
        $dbal
            ->addSelect('users_profile_info.status as users_profile_status')
            ->leftJoin(
                'avito_token',
                UserProfileInfo::class,
                'users_profile_info',
                'users_profile_info.profile = avito_token.id'
            );

        // Event
        $dbal->leftJoin(
            'users_profile',
            UserProfileEvent::class,
            'users_profile_event',
            'users_profile_event.id = users_profile.event'
        );


        // Personal
        $dbal->addSelect('users_profile_personal.username AS users_profile_username');

        $dbal->leftJoin(
            'users_profile_event',
            UserProfilePersonal::class,
            'users_profile_personal',
            'users_profile_personal.event = users_profile_event.id'
        );

        // Avatar

        $dbal->addSelect("CASE WHEN users_profile_avatar.name IS NOT NULL THEN CONCAT ( '/upload/" . $dbal->table(UserProfileAvatar::class) . "' , '/', users_profile_avatar.name) ELSE NULL END AS users_profile_avatar");
        $dbal->addSelect("users_profile_avatar.ext AS users_profile_avatar_ext");
        $dbal->addSelect('users_profile_avatar.cdn AS users_profile_avatar_cdn');

        $dbal->leftJoin(
            'users_profile_event',
            UserProfileAvatar::class,
            'users_profile_avatar',
            'users_profile_avatar.event = users_profile_event.id'
        );

        /** ACCOUNT */
        $dbal->leftJoin(
            'users_profile_info',
            Account::class,
            'account',
            'account.id = users_profile_info.usr'
        );

        $dbal->addSelect('account_event.email AS account_email');
        $dbal->leftJoin(
            'account',
            AccountEvent::class,
            'account_event',
            'account_event.id = account.event AND account_event.account = account.id'
        );

        $dbal->addSelect('account_status.status as account_status');
        $dbal->leftJoin(
            'account_event',
            AccountStatus::class,
            'account_status',
            'account_status.event = account_event.id'
        );

        /* Поиск */
        if ($this->search?->getQuery())
        {
            $dbal
                ->createSearchQueryBuilder($this->search)
                ->addSearchEqualUid('avito_token.id')
                ->addSearchEqualUid('avito_token.event')
                ->addSearchLike('account_event.email')
                ->addSearchLike('users_profile_personal.username');
        }

        return $this->paginator->fetchAllAssociative($dbal);

    }
}
