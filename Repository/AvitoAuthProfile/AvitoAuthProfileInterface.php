<?php

namespace BaksDev\Avito\Repository\AvitoAuthProfile;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

interface AvitoAuthProfileInterface
{
    public function findByProfile(UserProfileUid $profile): ?array;
}
