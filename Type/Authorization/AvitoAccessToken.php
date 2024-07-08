<?php

namespace BaksDev\Avito\Type\Authorization;

final class AvitoAccessToken
{
    public function __construct(
        private readonly string $accessToken,
        private readonly bool $cached,
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
