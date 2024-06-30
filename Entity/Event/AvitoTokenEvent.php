<?php

declare(strict_types=1);

namespace BaksDev\Avito\Entity\Event;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Modifier\AvitoTokenModify;
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
    private UserProfileUid $profileId;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    private string $clientId;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    private string $clientSecret;

    /**
     * Токен
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    private string $token;

    /**
     * Статус true = активен / false = заблокирован
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active = true;

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

    public function getProfileId(): UserProfileUid
    {
        return $this->profileId;
    }

    public function setMain(AvitoToken|UserProfileUid $main): self
    {
        $this->profileId = $main instanceof AvitoToken ? $main->getProfileId() : $main;

        return $this;
    }

    public function getDto($dto): mixed
    {
        if ($dto instanceof AvitoTokenEventInterface) {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if ($dto instanceof AvitoTokenEventInterface) {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}