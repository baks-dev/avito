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

namespace BaksDev\Avito\Repository\AllTokensByProfile;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\Active\AvitoTokenActive;
use BaksDev\Avito\Entity\Event\Name\AvitoTokenName;
use BaksDev\Avito\Entity\Event\Profile\AvitoTokenProfile;
use BaksDev\Avito\Type\Id\AvitoTokenUid;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;
use InvalidArgumentException;


final class AvitoTokensByProfileRepository implements AvitoTokensByProfileInterface
{
    private UserProfileUid|false $profile = false;

    private bool $active = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function forProfile(UserProfileUid $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function onlyActive(): self
    {
        $this->active = true;

        return $this;
    }

    /**
     * Метод возвращает идентификаторы токенов профиля пользователя
     *
     * @return Generator<AvitoTokenUid>|false
     */
    public function findAll(): Generator|false
    {
        if(false === ($this->profile instanceof UserProfileUid))
        {
            throw new InvalidArgumentException('Invalid Argument UserProfileUid');
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('avito_token.id AS value')
            ->from(AvitoToken::class, 'avito_token');

        $dbal->join(
            'avito_token',
            AvitoTokenProfile::class,
            'avito_token_profile',
            'avito_token_profile.event = avito_token.event 
            AND avito_token_profile.value = :profile',
        )
            ->setParameter(
                key: 'profile',
                value: $this->profile,
                type: UserProfileUid::TYPE,
            );


        if($this->active)
        {
            $dbal->join(
                'avito_token',
                AvitoTokenActive::class,
                'avito_token_active',
                'avito_token_active.event = avito_token.event 
                AND avito_token_active.value IS TRUE',
            );
        }

        $dbal
            ->leftJoin(
                'avito_token',
                AvitoTokenName::class,
                'avito_token_name',
                'avito_token_name.event = avito_token.event',
            );


        $dbal
            ->addSelect("JSONB_BUILD_OBJECT
		    (
		        'name', avito_token_name.value 
		    ) AS attr");

        return $dbal
            ->enableCache('avito', '1 day')
            ->fetchAllHydrate(AvitoTokenUid::class);
    }
}