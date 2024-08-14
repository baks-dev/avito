<?php

declare(strict_types=1);

namespace BaksDev\Avito\UseCase\Admin\Delete;

use BaksDev\Avito\Entity\Event\AvitoTokenEventInterface;
use BaksDev\Avito\Type\Event\AvitoTokenEventUid;
use BaksDev\Avito\UseCase\Admin\Delete\Modify\ModifyDTO;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Validator\Constraints as Assert;

final class AvitoTokenDeleteDTO implements AvitoTokenEventInterface
{
    #[Assert\Uuid]
    private ?AvitoTokenEventUid $id = null;

    #[Assert\NotBlank]
    private readonly UserProfileUid $profile;

    #[Assert\Valid]
    private Modify\ModifyDTO $modify;


    public function __construct()
    {
        $this->modify = new ModifyDTO();
    }

    public function setId(AvitoTokenEventUid $id): void
    {
        $this->id = $id;
    }

    public function getEvent(): ?AvitoTokenEventUid
    {
        return $this->id;
    }

    public function getModify(): Modify\ModifyDTO
    {
        return $this->modify;
    }

    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }

    public function setProfile(UserProfileUid $profile): void
    {
        $this->profile = $profile;
    }
}
