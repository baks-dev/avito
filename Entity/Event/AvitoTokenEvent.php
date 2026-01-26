<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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
use BaksDev\Avito\Entity\Event\Active\AvitoTokenActive;
use BaksDev\Avito\Entity\Event\Address\AvitoTokenAddress;
use BaksDev\Avito\Entity\Event\Client\AvitoTokenClient;
use BaksDev\Avito\Entity\Event\Kit\AvitoTokenKit;
use BaksDev\Avito\Entity\Event\Manager\AvitoTokenManager;
use BaksDev\Avito\Entity\Event\Name\AvitoTokenName;
use BaksDev\Avito\Entity\Event\Percent\AvitoTokenPercent;
use BaksDev\Avito\Entity\Event\Phone\AvitoTokenPhone;
use BaksDev\Avito\Entity\Event\Profile\AvitoTokenProfile;
use BaksDev\Avito\Entity\Event\Secret\AvitoTokenSecret;
use BaksDev\Avito\Entity\Event\User\AvitoTokenUser;
use BaksDev\Avito\Entity\Modifier\AvitoTokenModify;
use BaksDev\Avito\Type\Event\AvitoTokenEventUid;
use BaksDev\Avito\Type\Id\AvitoTokenUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/** @see AvitoTokenNewEditDTO */
#[ORM\Entity]
#[ORM\Table(name: 'avito_token_event')]
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
    #[ORM\Column(type: AvitoTokenUid::TYPE)]
    private AvitoTokenUid $main;


    /** Идентификатор профиля владельца */
    #[ORM\OneToOne(targetEntity: AvitoTokenProfile::class, mappedBy: 'event', cascade: ['all'])]
    private ?AvitoTokenProfile $profile = null;


    /** Название токена */
    #[ORM\OneToOne(targetEntity: AvitoTokenName::class, mappedBy: 'event', cascade: ['all'])]
    private ?AvitoTokenName $name = null;

    /** Идентификатор клиента (client_id) */
    #[ORM\OneToOne(targetEntity: AvitoTokenClient::class, mappedBy: 'event', cascade: ['all'])]
    private ?AvitoTokenClient $client = null;

    /** Пароль клиента (сlient_secret) */
    #[ORM\OneToOne(targetEntity: AvitoTokenSecret::class, mappedBy: 'event', cascade: ['all'])]
    private ?AvitoTokenSecret $secret = null;


    /** Номер профиля Avito (user_id) */
    #[ORM\OneToOne(targetEntity: AvitoTokenUser::class, mappedBy: 'event', cascade: ['all'])]
    private ?AvitoTokenUser $user = null;


    /** Настройка для администратора - вкл/выкл токен */
    #[ORM\OneToOne(targetEntity: AvitoTokenActive::class, mappedBy: 'event', cascade: ['all'])]
    private ?AvitoTokenActive $active = null;


    /** Торговая наценка площадки */
    #[ORM\OneToOne(targetEntity: AvitoTokenPercent::class, mappedBy: 'event', cascade: ['all'])]
    private ?AvitoTokenPercent $percent = null;

    #[ORM\OneToOne(targetEntity: AvitoTokenModify::class, mappedBy: 'event', cascade: ['all'])]
    private AvitoTokenModify $modify;

    /**
     * Настройки объявления
     */

    /** Адрес для объявлений */
    #[ORM\OneToOne(targetEntity: AvitoTokenAddress::class, mappedBy: 'event', cascade: ['all'])]
    private ?AvitoTokenAddress $address = null;

    /** Контактное лицо для связи */
    #[ORM\OneToOne(targetEntity: AvitoTokenManager::class, mappedBy: 'event', cascade: ['all'])]
    private ?AvitoTokenManager $manager = null;

    /** Контактный номер телефона */
    #[ORM\OneToOne(targetEntity: AvitoTokenPhone::class, mappedBy: 'event', cascade: ['all'])]
    private ?AvitoTokenPhone $phone = null;

    /** Настройка количества товаров в объявлении */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: AvitoTokenKit::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $kit;

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
        return $this->profile?->getValue();
    }

    public function setMain(AvitoToken|AvitoTokenUid $main): self
    {
        $this->main = $main instanceof AvitoToken ? $main->getId() : $main;

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
