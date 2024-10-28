<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

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
    #[ORM\Column(type: Types::STRING)]
    private string $client;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    private string $secret;

    /**
     * Номер профиля Avito
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private string $usr;

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
        return (string) $this->id;
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

        if($dto instanceof AvitoTokenEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof AvitoTokenEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}
