<?php

namespace BaksDev\Avito\Api;

final class AvitoAccessToken
{
    public function __construct(
        private string $accessToken,
        private ?string $expiresIn = null,
    ) {}

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getExpiresIn(): ?string
    {
        return $this->expiresIn;
    }
}
