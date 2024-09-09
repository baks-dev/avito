<?php

namespace BaksDev\Avito\Repository\OneUserProfilesByActiveToken;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

interface OneUserProfilesByActiveTokenInterface
{
    public function findByProfile(UserProfileUid $profile): UserProfileUid|false;
}
