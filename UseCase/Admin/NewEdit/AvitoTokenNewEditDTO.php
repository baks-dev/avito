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
    private string $client;

    /**
     * обнуляемое поля для сокрытия на форме
     */
    #[Assert\NotBlank]
    private ?string $secret = null;

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

    public function getClient(): string
    {
        return $this->client;
    }

    public function setClient(?string $client): void
    {
        $this->client = $client;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        // установить только не пустое значение
        if(!empty($secret))
        {
            $this->secret = $secret;
        }
    }

    public function hiddenSecret(): void
    {
        $this->secret = null;
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