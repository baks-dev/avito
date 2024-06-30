<?php

namespace BaksDev\Avito\Entity\Event;

use BaksDev\Avito\Type\Event\AvitoTokenEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

interface AvitoTokenEventInterface
{
    public function getEvent(): ?AvitoTokenEventUid;

    public function getProfile(): ?UserProfileUid;
}
