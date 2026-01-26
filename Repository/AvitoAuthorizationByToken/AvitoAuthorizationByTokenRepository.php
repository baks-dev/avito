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

namespace BaksDev\Avito\Repository\AvitoAuthorizationByToken;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\Active\AvitoTokenActive;
use BaksDev\Avito\Entity\Event\Client\AvitoTokenClient;
use BaksDev\Avito\Entity\Event\Percent\AvitoTokenPercent;
use BaksDev\Avito\Entity\Event\Profile\AvitoTokenProfile;
use BaksDev\Avito\Entity\Event\Secret\AvitoTokenSecret;
use BaksDev\Avito\Entity\Event\User\AvitoTokenUser;
use BaksDev\Avito\Type\Authorization\AvitoTokenAuthorization;
use BaksDev\Avito\Type\Id\AvitoTokenUid;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use InvalidArgumentException;

final  class AvitoAuthorizationByTokenRepository implements AvitoAuthorizationByTokenInterface
{
    private AvitoTokenUid|false $token = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    public function forAvitoToken(AvitoTokenUid $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getAuthorization(): AvitoTokenAuthorization|false
    {
        if(false === ($this->token instanceof AvitoTokenUid))
        {
            throw new InvalidArgumentException('Invalid Argument AvitoTokenUid');
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(AvitoToken::class, 'avito_token')
            ->where('avito_token.id = :token')
            ->setParameter(
                key: 'token',
                value: $this->token,
                type: AvitoTokenUid::TYPE,
            );

        $dbal->join(
            'avito_token',
            AvitoTokenActive::class,
            'avito_token_active',
            '
            avito_token_active.event = avito_token.event AND 
            avito_token_active.value IS TRUE',
        );

        $dbal->join(
            'avito_token',
            AvitoTokenClient::class,
            'avito_token_client',
            'avito_token_client.event = avito_token.event',
        );

        $dbal->join(
            'avito_token',
            AvitoTokenProfile::class,
            'avito_token_profile',
            'avito_token_profile.event = avito_token.event',
        );

        $dbal->join(
            'avito_token',
            AvitoTokenUser::class,
            'avito_token_user',
            'avito_token_user.event = avito_token.event',
        );

        $dbal->join(
            'avito_token',
            AvitoTokenSecret::class,
            'avito_token_secret',
            'avito_token_secret.event = avito_token.event',
        );

        $dbal->join(
            'avito_token',
            AvitoTokenPercent::class,
            'avito_token_percent',
            'avito_token_percent.event = avito_token.event',
        );

        $dbal
            ->leftJoin(
                'avito_token',
                UserProfileInfo::class,
                'info',
                'info.profile = avito_token.id AND info.status = :status',
            )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE,
            );

        $dbal
            ->select('avito_token.id AS token')
            ->addSelect('avito_token_profile.value AS profile')
            ->addSelect('avito_token_client.value AS client')
            ->addSelect('avito_token_secret.value AS secret')
            ->addSelect('avito_token_user.value AS user')
            ->addSelect('avito_token_percent.value AS percent');

        /* Кешируем результат ORM */
        return $dbal
            ->enableCache('avito')
            ->fetchHydrate(AvitoTokenAuthorization::class);
    }
}
