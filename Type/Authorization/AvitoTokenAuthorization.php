<?php

namespace BaksDev\Avito\Type\Authorization;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class AvitoTokenAuthorization
{
    private readonly UserProfileUid $profile;

    public function __construct(
        UserProfileUid|string $profile,
        private readonly string $client,
        private readonly string $secret,
        private readonly string $user,
    )
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;
    }

    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }

    public function getClient(): string
    {
        return $this->client;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getUser(): string
    {
        return $this->user;
    }
}
