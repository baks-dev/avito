<?php

namespace BaksDev\Avito\Repository\AvitoTokenByProfile;

use BaksDev\Avito\Type\Authorization\AvitoTokenAuthorization;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

interface AvitoTokenByProfileRepositoryInterface
{
    public function getAuthorization(UserProfileUid $profile): ?AvitoTokenAuthorization;
}