<?php

declare(strict_types=1);

namespace BaksDev\Avito\UseCase\Admin\NewEdit;

use BaksDev\Avito\Entity\Event\AvitoTokenEventInterface;
use BaksDev\Avito\Type\Event\AvitoTokenEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Validator\Constraints as Assert;

final class AvitoTokenNewEditDTO implements AvitoTokenEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?AvitoTokenEventUid $id = null;

    /**
     * ID настройки (профиль пользователя)
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private ?UserProfileUid $profile = null;

    #[Assert\NotBlank]
    private string $clientId;

    /**
     * обнуляемое поля для сокрытия на форме
     */
    #[Assert\NotBlank]
    private ?string $clientSecret = null;

    private bool $active = true;

    public function setId(?AvitoTokenEventUid $id): void
    {
        $this->id = $id;
    }

    public function getEvent(): ?AvitoTokenEventUid
    {
        return $this->id;
    }

    public function setProfile(UserProfileUid $profile): void
    {
        $this->profile = $profile;
    }

    public function getProfile(): ?UserProfileUid
    {
        return $this->profile;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function setClientId(?string $clientId): void
    {
        $this->clientId = $clientId;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function setClientSecret(?string $clientSecret): void
    {
        // установить только не пустое значение
        if(!empty($clientSecret))
        {
            $this->clientSecret = $clientSecret;
        }
    }

    public function hiddenSecret(): void
    {
        $this->clientSecret = null;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}