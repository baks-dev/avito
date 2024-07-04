<?php

namespace BaksDev\Avito\Api;

// абстрактный класс для взаимодействия с avito api
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;

abstract class AvitoApi
{
    private array $headers;

    protected readonly LoggerInterface $logger;

    protected ?UserProfileUid $profile = null;


    public function __construct(
        LoggerInterface $yandexMarketLogger,
        private readonly AvitoTokenRequest $tokenRequest,
    ) {
        $this->logger = $yandexMarketLogger;
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

    public function tokenHttpClient(): RetryableHttpClient
    {
        if (null === $this->profile)
        {
            $this->logger->critical('Не указан идентификатор профиля пользователя через вызов метода profile', [__FILE__ . ':' . __LINE__]);

            throw new InvalidArgumentException(
                'Не указан идентификатор профиля пользователя через вызов метода profile: ->profile($UserProfileUid)'
            );
        }

        $token = $this->tokenRequest->getToken($this->profile);

        $this->headers = ['Authorization' => 'Bearer ' . $token->getAccessToken()];

        return new RetryableHttpClient(
            HttpClient::create(['headers' => $this->headers])
                ->withOptions([
                    'base_uri' => 'https://api.avito.ru',
                    'verify_host' => false,
                ])
        );
    }

}
