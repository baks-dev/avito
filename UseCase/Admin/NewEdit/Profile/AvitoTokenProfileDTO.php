<?php

declare(strict_types=1);

namespace BaksDev\Avito\UseCase\Admin\NewEdit\Profile;

use BaksDev\Avito\Entity\Profile\AvitoTokenProfileInterface;
use Symfony\Component\Validator\Constraints as Assert;

/** @see AvitoTokenEvent */
final class AvitoTokenProfileDTO implements AvitoTokenProfileInterface
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 256, maxMessage: 'Превышена максимальная длинна')]
    private string $address;

    #[Assert\NotBlank]
    #[Assert\Length(max: 40, maxMessage: 'Превышена максимальная длинна')]
    private string $manager;

    #[Assert\NotBlank]
    private string $phone;

    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 100)]
    private int $percent = 0;

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getManager(): string
    {
        return $this->manager;
    }

    public function setManager(string $manager): void
    {
        $this->manager = $manager;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getPercent(): int
    {
        return $this->percent;
    }

    public function setPercent(int $percent): void
    {
        $this->percent = $percent;
    }
}
