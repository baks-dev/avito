<?php

namespace BaksDev\Avito\Api;

final class AvitoAccessToken
{
    public function __construct(
        private string $accessToken,
        private bool $cached = false,
    ) {}

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function isCached(): bool
    {
        return $this->cached;
    }
}
