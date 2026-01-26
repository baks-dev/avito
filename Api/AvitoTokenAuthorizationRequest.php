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

namespace BaksDev\Avito\Api;

use BaksDev\Avito\Repository\AvitoAuthorizationByProfile\AvitoAuthorizationByProfileInterface;
use BaksDev\Avito\Repository\AvitoAuthorizationByToken\AvitoAuthorizationByTokenInterface;
use BaksDev\Avito\Type\Authorization\AvitoAccessToken;
use BaksDev\Avito\Type\Authorization\AvitoTokenAuthorization;
use BaksDev\Avito\Type\Id\AvitoTokenUid;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use DateInterval;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;

#[Autoconfigure(public: true)]
final class AvitoTokenAuthorizationRequest
{
    public function __construct(
        #[Target('avitoLogger')] private LoggerInterface $logger,
        private readonly AppCacheInterface $cache,
        private readonly AvitoAuthorizationByTokenInterface $AvitoAuthorizationByTokenRepository,
        private AvitoTokenAuthorization|false $authorization = false,
    ) {}

    public function getToken(
        AvitoTokenUid $token,
        AvitoTokenAuthorization|false $authorization = false,
    ): AvitoAccessToken
    {

        // параметр передается для тестирования
        if(false !== $authorization)
        {
            $this->authorization = $authorization;
        }

        if(false === $this->authorization)
        {
            $authorization = $this->AvitoAuthorizationByTokenRepository
                ->forAvitoToken($token)
                ->getAuthorization();

            if(false === $authorization)
            {
                throw new DomainException(sprintf('Авторизационные данные для получения токена Avito не найден по профилю: %s', $token));
            }

            $this->authorization = $authorization;

        }

        $cache = $this->cache->init('avito');

        $item = $cache->getItem('avito-token-'.$token->getValue());

        if(false === $item->isHit())
        {
            $client = new RetryableHttpClient(
                HttpClient::create(['headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ]])
                    ->withOptions([
                        'base_uri' => 'https://api.avito.ru',
                        'verify_host' => false,
                        'body' => [
                            'grant_type' => 'client_credentials',
                            'client_id' => $this->authorization->getClient(),
                            'client_secret' => $this->authorization->getSecret(),
                        ],
                    ]),
            );

            $response = $client->request('POST', '/token');

            /**
             * Получения временного ключа для авторизации
             *
             * @see https://developers.avito.ru/api-catalog/auth/documentation#operation/getAccessToken
             */
            $result = $response->toArray(false);

            if(array_key_exists('error', $result))
            {
                $this->logger->critical($result['error'].': '.$result['error_description'], [__FILE__.':'.__LINE__]);

                throw new DomainException(message: 'Ошибка получения токена авторизации от Avito Api', code: $response->getStatusCode());
            }

            if($response->getStatusCode() !== 200)
            {
                throw new DomainException(message: 'Ошибка получения токена авторизации от Avito Api', code: $response->getStatusCode());
            }

            $refreshToken = new AvitoAccessToken($result['access_token'], false);

            $item->expiresAfter(DateInterval::createFromDateString($result['expires_in'].' seconds'));
            $item->set($refreshToken->getAccessToken());
            $cache->save($item);

            return $refreshToken;
        }

        $token = $item->get() ?? throw new DomainException(message: 'Ошибка получения токена авторизации из кеша');

        return new AvitoAccessToken($token, true);
    }

    /**
     * Метод возвращает идентификатор профиля пользователя
     */
    public function getTokenIdentifier(): string
    {
        return $this->authorization->getToken();
    }

    /**
     * Метод возвращает идентификатор профиля пользователя
     */
    public function getProfile(): string
    {
        return $this->authorization->getProfile();
    }


    /**
     * Метод возвращает идентификатор клиента токена
     */
    public function getClient(): string
    {
        return $this->authorization->getClient();
    }


    /**
     * Метод возвращает идентификатор клиента токена
     */
    public function getPercent(): string
    {
        return $this->authorization->getPercent();
    }

    /**
     * Метод возвращает идентификатор пользователя Avito
     */
    public function getUser(): string
    {
        return $this->authorization->getUser();
    }
}
