<?php

declare(strict_types=1);

namespace BaksDev\Avito\Entity\Profile;

use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Core\Entity\EntityEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'avito_token_profile')]
class AvitoTokenProfile extends EntityEvent
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: AvitoTokenEvent::class, inversedBy: 'tokenProfile')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private AvitoTokenEvent $event;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    private string $address;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    private string $manager;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    private string $phone;

    /**
     * Торговая наценка площадки
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $percent = 0;

    public function __construct(AvitoTokenEvent $event)
    {
        $this->event = $event;
    }

    public function __toString(): string
    {
        return (string) $this->event;
    }

    public function getDto($dto): mixed
    {
        if ($dto instanceof AvitoTokenProfileInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if ($dto instanceof AvitoTokenProfileInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}
