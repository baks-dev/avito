<?php

namespace BaksDev\Avito\Api;

// абстрактный класс для взаимодействия с avito api
use BaksDev\Avito\Repository\AvitoTokenByProfile\AvitoTokenByProfileRepositoryInterface;
use BaksDev\Avito\Type\Authorization\AvitoTokenAuthorization;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use DomainException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;

abstract class AvitoApi
{
    private array $headers;

    protected readonly LoggerInterface $logger;

    protected ?UserProfileUid $profile = null;

    // данные для получения токена
    private ?AvitoTokenAuthorization $authorization = null;

    public function __construct(
        private readonly AvitoTokenByProfileRepositoryInterface $tokenByProfile,
        private readonly AppCacheInterface $cache,
        LoggerInterface $yandexMarketLogger,
    )
    {
        $this->logger = $yandexMarketLogger;
    }

    public function profile(UserProfileUid|string $profile): self
    {
        if (is_string($profile)) {

            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;

        $this->authorization = $this->tokenByProfile->getAuthorization($this->profile);

        return $this;
    }

    public function tokenHttpClient(AvitoTokenAuthorization $authorizationToken = null): RetryableHttpClient
    {
        // для тестирования
        if ($authorizationToken !== null) {
            $this->authorization = $authorizationToken;
            $this->profile = $authorizationToken->getProfile();
        }

        if (null === $this->profile) {
            $this->logger->critical('Не указан идентификатор профиля пользователя через вызов метода profile', [__FILE__ . ':' . __LINE__]);

            throw new InvalidArgumentException(
                'Не указан идентификатор профиля пользователя через вызов метода profile: ->profile($UserProfileUid)'
            );
        }

        if (null === $this->authorization) {
            $this->authorization = $this->tokenByProfile->getAuthorization($this->profile) ?? throw new DomainException(sprintf('Авторизационные данные для получения токена Avito не найден: %s', $this->profile));
        }

        $cache = $this->cache->init('avito', 86400);

        /** @var AvitoAccessToken $token */
        $token = $cache->get('avito-token' . $this->profile->getValue(), function (): AvitoAccessToken {

            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];

            $body = [
                'grant_type' => 'client_credentials',
                'client_id' => $this->authorization->getClient(),
                'client_secret' => $this->authorization->getSecret(),
            ];

            $client = $this->httpClient($headers, $body);

            $response = $client->request(
                'POST',
                '/token');

            $result = $response->toArray(false);

            if ($response->getStatusCode() !== 200) {
                foreach ($result as $error) {
                    $this->logger->critical($error['code'] . ': ' . $error['message'], [__FILE__ . ':' . __LINE__]);
                }

                throw new DomainException(message: 'Ошибка получения токена авторизации Avito', code: $response->getStatusCode());
            }

            return new AvitoAccessToken($result['access_token'], $result['expires_in']);
        });

        $this->headers = ['Authorization' => 'Bearer ' . $token->getAccessToken()];

        return $this->httpClient($this->headers);
    }

    private function httpClient(array $headers, array $body = null): RetryableHttpClient
    {
        return new RetryableHttpClient(
            HttpClient::create(['headers' => $headers])
                ->withOptions([
                    'base_uri' => 'https://api.avito.ru',
                    'verify_host' => false,
                    'body' => $body
                ])
        );
    }
}