<?php

namespace BaksDev\Avito\Api;

use BaksDev\Avito\Repository\AvitoAuthorizationByProfile\AvitoAuthorizationByProfileInterface;
use BaksDev\Avito\Type\Authorization\AvitoTokenAuthorization;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;

#[Autoconfigure(public: true)]
final class AvitoTokenRequest
{
    protected readonly LoggerInterface $logger;

    public function __construct(
        LoggerInterface $avitoLogger,
        private readonly AppCacheInterface $cache,
        private readonly AvitoAuthorizationByProfileInterface $authorizationByProfile,
        private ?AvitoTokenAuthorization $authorization = null,
    ) {
        $this->logger = $avitoLogger;
    }

    public function getToken(UserProfileUid $profile, AvitoTokenAuthorization $authorization = null): AvitoAccessToken
    {
        // для тестирования
        if (null !== $authorization)
        {
            $this->authorization = $authorization;
        }

        if (null === $this->authorization)
        {

            $authorization = $this->authorizationByProfile->getAuthorization($profile);

            if (null === $authorization)
            {
                throw new DomainException(sprintf('Авторизационные данные для получения токена Avito не найден по профилю: %s', $profile));
            }

            $this->authorization = $authorization;
        }

        $cache = $this->cache->init('avito', 86400);

        $cachePool = $cache->getItem('avito-token-' . $profile->getValue());

        if (false === $cachePool->isHit())
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
                        ]
                    ])
            );

            $response = $client->request(
                'POST',
                '/token'
            );

            $result = $response->toArray(false);

            if ($response->getStatusCode() !== 200)
            {
                foreach ($result as $error)
                {
                    $this->logger->critical($error['code'] . ': ' . $error['message'], [__FILE__ . ':' . __LINE__]);
                }

                throw new DomainException(message: 'Ошибка получения токена авторизации от Avito Api', code: $response->getStatusCode());
            }

            $refreshToken = new AvitoAccessToken($result['access_token']);
            $cachePool->set($refreshToken->getAccessToken());
            $cache->save($cachePool);

            return $refreshToken;
        }

        $token = $cachePool->get() ?? throw new DomainException(message: 'Ошибка получения токена авторизации из кеша');

        return new AvitoAccessToken($token, true);
    }
}
