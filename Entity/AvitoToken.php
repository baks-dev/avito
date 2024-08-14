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
    private UserProfileUid $id;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: AvitoTokenEventUid::TYPE, unique: true, nullable: false)]
    private AvitoTokenEventUid $event;

    public function __construct(UserProfile|UserProfileUid $profile)
    {
        $this->id = $profile instanceof UserProfile ? $profile->getId() : $profile;
    }

    public function __toString(): string
    {
        return (string)$this->id;
    }

    public function getId(): UserProfileUid
    {
        return $this->id;
    }

    public function getEvent(): AvitoTokenEventUid
    {
        return $this->event;
    }

    public function setEvent(AvitoTokenEvent|AvitoTokenEventUid $eventId): void
    {
        $this->event = $eventId instanceof AvitoTokenEvent ? $eventId->getId() : $eventId;
    }
}
