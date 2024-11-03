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

namespace BaksDev\Avito\Repository\AllUserProfilesByActiveToken;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use Generator;

final readonly class AllUserProfilesByTokenRepository implements AllUserProfilesByActiveTokenInterface
{
    public function __construct(
        private DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    public function findProfilesByActiveToken(): Generator
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

        /** Информация о профиле */
        $dbal
            ->leftJoin(
                'avito_token',
                UserProfile::class,
                'users_profile',
                'users_profile.id = avito_token.id'
            );

        $dbal
            ->join(
                'avito_token',
                UserProfileInfo::class,
                'users_profile_info',
                'users_profile_info.profile = avito_token.id AND users_profile_info.status = :status',
            )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE
            );


        $dbal->leftJoin(
            'users_profile',
            UserProfilePersonal::class,
            'personal',
            'personal.event = users_profile.event',
        );


        /** Параметры конструктора объекта гидрации */
        $dbal->select('avito_token.id as value');
        $dbal->addSelect('personal.username AS attr');

        return $dbal
            ->enableCache('avito', '1 minutes')
            ->fetchAllHydrate(UserProfileUid::class);
    }
}
