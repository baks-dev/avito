<?php

declare(strict_types=1);

namespace BaksDev\Avito\Entity\Event;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Modifier\AvitoTokenModify;
use BaksDev\Avito\Entity\Profile\AvitoTokenProfile;
use BaksDev\Avito\Type\Event\AvitoTokenEventUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'avito_token_event')]
#[ORM\Index(columns: ['profile', 'active'])]
class AvitoTokenEvent extends EntityEvent
{
    /**
     * Идентификатор События
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: AvitoTokenEventUid::TYPE)]
    private AvitoTokenEventUid $id;

    /**
     * ID профиля пользователя
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private UserProfileUid $profile;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    private string $client;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    private string $secret;

    /**
     * Настройка для администратора - вкл/выкл токен
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active = true;

    #[ORM\OneToOne(targetEntity: AvitoTokenProfile::class, mappedBy: 'event', cascade: ['all'])]
    private AvitoTokenProfile $tokenProfile;

    #[ORM\OneToOne(targetEntity: AvitoTokenModify::class, mappedBy: 'event', cascade: ['all'])]
    private AvitoTokenModify $modify;

    public function __construct()
    {
        $this->id = new AvitoTokenEventUid();
        $this->modify = new AvitoTokenModify($this);
    }

    public function __clone()
    {
        $this->id = clone new AvitoTokenEventUid();
    }

    public function __toString(): string
    {
        return (string)$this->id;
    }

    public function getId(): AvitoTokenEventUid
    {
        return $this->id;
    }

    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }

    public function getTokenProfile(): AvitoTokenProfile
    {
        return $this->tokenProfile;
    }

    public function setMain(AvitoToken|UserProfileUid $main): self
    {
        $this->profile = $main instanceof AvitoToken ? $main->getId() : $main;

        return $this;
    }

    public function getDto($dto): mixed
    {

        if ($dto instanceof AvitoTokenEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if ($dto instanceof AvitoTokenEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}
