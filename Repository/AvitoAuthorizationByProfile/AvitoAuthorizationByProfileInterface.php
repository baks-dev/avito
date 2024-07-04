<?php

namespace BaksDev\Avito\Repository\AvitoAuthorizationByProfile;

use BaksDev\Avito\Type\Authorization\AvitoTokenAuthorization;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

interface AvitoAuthorizationByProfileInterface
{
    public function getAuthorization(UserProfileUid $profile): ?AvitoTokenAuthorization;
}