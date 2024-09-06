<?php

namespace BaksDev\Avito\Api;

use BaksDev\Avito\Repository\AvitoAuthorizationByProfile\AvitoAuthorizationByProfileInterface;
use BaksDev\Avito\Type\Authorization\AvitoAccessToken;
use BaksDev\Avito\Type\Authorization\AvitoTokenAuthorization;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use DateInterval;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\Mailer\Exception\TransportException;

#[Autoconfigure(public: true)]
final class AvitoTokenAuthorizationRequest
{
    private readonly LoggerInterface $logger;

    public function __construct(
        LoggerInterface $avitoTokenLogger,
        private readonly AppCacheInterface $cache,
        private readonly AvitoAuthorizationByProfileInterface $authorizationByProfile,
        private ?AvitoTokenAuthorization $authorization = null,
    ) {
        $this->logger = $avitoTokenLogger;
    }

    public function getToken(UserProfileUid $profile, AvitoTokenAuthorization $authorization = null): AvitoAccessToken
    {
        // параметр передается для тестирования
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

        $cache = $this->cache->init('avito');

        $item = $cache->getItem('avito-token-' . $profile->getValue());

        if (false === $item->isHit())
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

            /**
             * ответ от avito api -
             * @see https://developers.avito.ru/api-catalog/auth/documentation#operation/getAccessToken
             */
            $result = $response->toArray(false);

            if (array_key_exists('error', $result))
            {
                $this->logger->critical($result['error'] . ': ' . $result['error_description'], [__FILE__ . ':' . __LINE__]);

                throw new DomainException(message: 'Ошибка получения токена авторизации от Avito Api', code: $response->getStatusCode());
            }

            if ($response->getStatusCode() !== 200)
            {
                throw new DomainException(message: 'Ошибка получения токена авторизации от Avito Api', code: $response->getStatusCode());
            }

            $refreshToken = new AvitoAccessToken($result['access_token'], false);

            $item->expiresAfter(DateInterval::createFromDateString($result['expires_in'] . ' seconds'));
            $item->set($refreshToken->getAccessToken());
            $cache->save($item);

            return $refreshToken;
        }

        $token = $item->get() ?? throw new DomainException(message: 'Ошибка получения токена авторизации из кеша');

        return new AvitoAccessToken($token, true);
    }
}
