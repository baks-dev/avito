<?php

declare(strict_types=1);

namespace BaksDev\Avito\Entity;

use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Type\Event\AvitoTokenEventUid;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'avito_token')]
class AvitoToken
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private UserProfileUid $profileId;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: AvitoTokenEventUid::TYPE, unique: true)]
    private AvitoTokenEventUid $eventId;

    public function __construct(UserProfile|UserProfileUid $profile)
    {
        $this->profileId = $profile instanceof UserProfile ? $profile->getId() : $profile;
    }

    public function __toString(): string
    {
        return (string)$this->profileId;
    }

    public function getProfileId(): UserProfileUid
    {
        return $this->profileId;
    }

    public function getEventId(): AvitoTokenEventUid
    {
        return $this->eventId;
    }

    public function setEventId(AvitoTokenEvent|AvitoTokenEventUid $eventId): void
    {
        $this->eventId = $eventId instanceof AvitoTokenEvent ? $eventId->getId() : $eventId;
    }
}