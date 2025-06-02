<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Avito\Api;

// абстрактный класс для взаимодействия с avito api
use BaksDev\Avito\Type\Authorization\AvitoTokenAuthorization;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\Cache\CacheInterface;

abstract class AvitoApi
{
    private array $headers;

    protected UserProfileUid|false $profile = false;

    public function __construct(
        #[Autowire(env: 'APP_ENV')] private readonly string $environment,
        #[Target('avitoLogger')] protected readonly LoggerInterface $logger,
        private readonly AppCacheInterface $cache,
        private readonly AvitoTokenAuthorizationRequest $authorizationRequest,
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

    public function tokenHttpClient(AvitoTokenAuthorization|false $authorization = false): RetryableHttpClient
    {
        /**
         * @note AvitoTokenAuthorization $authorization передается в тестовом окружении
         * Если передан тестовый authorization - присваиваем тестовый профиль
         */
        if(false !== $authorization)
        {
            $this->profile = $authorization->getProfile();
        }

        if(false === $this->profile)
        {
            $this->logger->critical('Не указан идентификатор профиля пользователя через вызов метода profile', [__FILE__.':'.__LINE__]);

            throw new InvalidArgumentException(
                'Не указан идентификатор профиля пользователя через вызов метода profile: ->profile($UserProfileUid)',
            );
        }

        /**
         * Получаем временный токен Авито
         * @note $authorization может быть передан в тестовом окружении, в противном случае всегда false
         */
        $token = $this->authorizationRequest->getToken($this->profile, $authorization);

        $this->headers = ['Authorization' => 'Bearer '.$token->getAccessToken()];

        return new RetryableHttpClient(
            HttpClient::create(['headers' => $this->headers])
                ->withOptions([
                    'base_uri' => 'https://api.avito.ru',
                    'verify_host' => false,
                ]),
        );
    }

    public function getClient(): string
    {
        return $this->authorizationRequest->getClient();
    }

    /**
     * Метод возвращает идентификатор клиента токена профиля пользователя
     */
    public function getUser(): int
    {
        return (int) $this->authorizationRequest->getUser();
    }

    /**
     * Метод возвращает Торговую наценку профиля
     */
    public function getPercent(): string
    {
        return $this->authorizationRequest->getPercent();
    }


    public function getCacheInit(string $namespace): CacheInterface
    {
        return $this->cache->init($namespace);
    }

    /**
     * Метод проверяет что окружение является PROD,
     * тем самым позволяет выполнять операции запроса на сторонний сервис
     * ТОЛЬКО в PROD окружении
     */
    protected function isExecuteEnvironment(): bool
    {
        return $this->environment === 'prod';
    }
}
