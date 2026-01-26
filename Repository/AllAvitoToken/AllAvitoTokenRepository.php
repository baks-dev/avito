<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Avito\Repository\AllAvitoToken;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\Active\AvitoTokenActive;
use BaksDev\Avito\Entity\Event\Name\AvitoTokenName;
use BaksDev\Avito\Entity\Event\Profile\AvitoTokenProfile;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorage;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class AllAvitoTokenRepository implements AllAvitoTokenInterface
{
    private ?SearchDTO $search = null;

    private ?UserProfileUid $profile = null;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
        private readonly UserProfileTokenStorageInterface $userProfileTokenStorage,
    ) {}

    public function profile(UserProfileUid|string $profile): self
    {
        if(is_string($profile))
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

    public function findPaginator(): PaginatorInterface
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('avito_token.id')
            ->addSelect('avito_token.event')
            ->from(AvitoToken::class, 'avito_token');

        $dbal->join(
            'avito_token',
            AvitoTokenProfile::class,
            'avito_token_profile',
            '
                avito_token_profile.event = avito_token.event
                AND avito_token_profile.value = :profile
             ');


        $dbal
            ->setParameter(
                key: 'profile',
                value: $this->profile instanceof UserProfileUid ? $this->profile : $this->userProfileTokenStorage->getProfile(),
                type: UserProfileUid::TYPE,
            );

        $dbal
            ->addSelect('avito_token_name.value AS name')
            ->leftJoin(
                'avito_token',
                AvitoTokenName::class,
                'avito_token_name',
                "avito_token_name.event = avito_token.event",
            );


        $dbal
            ->addSelect('avito_token_active.value AS active')
            ->leftJoin(
                'avito_token',
                AvitoTokenActive::class,
                'avito_token_active',
                "avito_token_active.event = avito_token.event",
            );


        // ОТВЕТСТВЕННЫЙ

        // UserProfile
        $dbal
            //->addSelect('users_profile.id as users_profile_id')
            ->addSelect('users_profile.event as users_profile_event')
            ->leftJoin(
                'avito_token',
                UserProfile::class,
                'users_profile',
                'users_profile.id = avito_token_profile.value',
            );


        // Info
        $dbal
            ->addSelect('users_profile_info.status as users_profile_status')
            ->leftJoin(
                'avito_token',
                UserProfileInfo::class,
                'users_profile_info',
                'users_profile_info.profile = avito_token_profile.value',
            );

        // Personal
        $dbal
            ->addSelect('users_profile_personal.username AS users_profile_username')
            ->leftJoin(
                'users_profile',
                UserProfilePersonal::class,
                'users_profile_personal',
                'users_profile_personal.event = users_profile.event',
            );

        // Avatar

        $dbal
            ->addSelect("CASE WHEN users_profile_avatar.name IS NOT NULL THEN CONCAT ( '/upload/".$dbal->table(UserProfileAvatar::class)."' , '/', users_profile_avatar.name) ELSE NULL END AS users_profile_avatar")
            ->addSelect("users_profile_avatar.ext AS users_profile_avatar_ext")
            ->addSelect('users_profile_avatar.cdn AS users_profile_avatar_cdn')
            ->leftJoin(
                'users_profile',
                UserProfileAvatar::class,
                'users_profile_avatar',
                'users_profile_avatar.event = users_profile.event',
            );

        /** ACCOUNT */
        $dbal->leftJoin(
            'users_profile_info',
            Account::class,
            'account',
            'account.id = users_profile_info.usr',
        );

        $dbal
            ->addSelect('account_event.email AS account_email')
            ->leftJoin(
                'account',
                AccountEvent::class,
                'account_event',
                'account_event.id = account.event AND account_event.account = account.id',
            );

        $dbal
            ->addSelect('account_status.status as account_status')
            ->leftJoin(
                'account_event',
                AccountStatus::class,
                'account_status',
                'account_status.event = account_event.id',
            );

        /* Поиск */
        if($this->search?->getQuery())
        {
            $dbal
                ->createSearchQueryBuilder($this->search)
                ->addSearchEqualUid('avito_token.id')
                ->addSearchEqualUid('avito_token.event')
                ->addSearchLike('account_event.email')
                ->addSearchLike('users_profile_personal.username');
        }

        return $this->paginator->fetchAllHydrate($dbal, AvitoTokensResult::class);
    }
}
