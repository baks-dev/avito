<?php

namespace BaksDev\Avito\Api;

// абстрактный класс для взаимодействия с avito api
use BaksDev\Avito\Type\Authorization\AvitoTokenAuthorization;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\Cache\CacheInterface;

abstract class AvitoApi
{
    private array $headers;

    protected readonly LoggerInterface $logger;

    protected UserProfileUid|false $profile = false;

    public function __construct(
        #[Autowire(env: 'APP_ENV')] private readonly string $environment,
        LoggerInterface $avitoTokenLogger,
        private readonly AppCacheInterface $cache,
        private readonly AvitoTokenAuthorizationRequest $authorizationRequest,
    )
    {
        $this->logger = $avitoTokenLogger;
    }

    public function profile(UserProfileUid|string $profile): self
    {
        if (is_string($profile))
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
        if (false !== $authorization)
        {
            $this->profile = $authorization->getProfile();
        }

        if (false === $this->profile)
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
