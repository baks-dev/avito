<?php

declare(strict_types=1);

namespace BaksDev\Avito\Messenger;

use BaksDev\Avito\Type\Event\AvitoTokenEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final readonly class AvitoTokenMessage
{
    public function __construct(
        private UserProfileUid $id,
        private AvitoTokenEventUid $currentEvent,
        private ?AvitoTokenEventUid $lastEvent = null,
    ) {}

    /**
     * Идентификатор
     */
    public function getId(): UserProfileUid
    {
        return $this->id;
    }

    /**
     * Идентификатор события
     */
    public function getCurrentEvent(): AvitoTokenEventUid
    {
        return $this->currentEvent;
    }

    /**
     * Идентификатор предыдущего события
     */
    public function getLastEvent(): ?AvitoTokenEventUid
    {
        return $this->lastEvent;
    }
}
