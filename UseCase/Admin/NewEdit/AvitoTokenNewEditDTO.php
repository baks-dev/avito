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

namespace BaksDev\Avito\UseCase\Admin\NewEdit;

use BaksDev\Avito\Entity\Event\AvitoTokenEventInterface;
use BaksDev\Avito\Type\Event\AvitoTokenEventUid;
use BaksDev\Avito\UseCase\Admin\NewEdit\Active\AvitoTokenActiveDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Address\AvitoTokenAddressDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Client\AvitoTokenClientDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Kit\AvitoTokenKitDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Manager\AvitoTokenManagerDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Name\AvitoTokenNameDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Percent\AvitoTokenPercentDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Phone\AvitoTokenPhoneDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Profile\AvitoTokenProfileDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\Secret\AvitoTokenSecretDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\User\AvitoTokenUserDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see AvitoTokenEvent */
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
    //    #[Assert\NotBlank]
    //    #[Assert\Uuid]
    #[Assert\Valid]
    private AvitoTokenProfileDTO $profile;

    #[Assert\Valid]
    private AvitoTokenNameDTO $name;

    #[Assert\Valid]
    private AvitoTokenActiveDTO $active;

    #[Assert\Valid]
    private AvitoTokenClientDTO $client;

    #[Assert\Valid]
    private AvitoTokenManagerDTO $manager;

    #[Assert\Valid]
    private AvitoTokenPercentDTO $percent;

    #[Assert\Valid]
    private AvitoTokenPhoneDTO $phone;

    #[Assert\Valid]
    private AvitoTokenSecretDTO $secret;

    #[Assert\Valid]
    private AvitoTokenUserDTO $user;

    #[Assert\Valid]
    private AvitoTokenAddressDTO $address;

    /**
     * Настройка количества товаров в объявлении
     *
     * @var ArrayCollection<int, AvitoTokenKitDTO> $kit
     */
    #[Assert\Valid]
    private ArrayCollection $kit;

    public function __construct()
    {
        $this->profile = new AvitoTokenProfileDTO();
        $this->name = new AvitoTokenNameDTO;
        $this->active = new AvitoTokenActiveDTO;
        $this->address = new AvitoTokenAddressDTO();
        $this->client = new AvitoTokenClientDTO;
        $this->manager = new AvitoTokenManagerDTO;
        $this->percent = new AvitoTokenPercentDTO;
        $this->phone = new AvitoTokenPhoneDTO;
        $this->secret = new AvitoTokenSecretDTO;
        $this->user = new AvitoTokenUserDTO;
        $this->kit = new ArrayCollection();

    }


    public function setId(?AvitoTokenEventUid $id): void
    {


        $this->id = $id;
    }

    public function getEvent(): ?AvitoTokenEventUid
    {
        return $this->id;
    }

    public function getProfile(): AvitoTokenProfileDTO
    {
        return $this->profile;
    }

    public function setProfile(AvitoTokenProfileDTO $profile): self
    {
        $this->profile = $profile;
        return $this;
    }

    public function getActive(): AvitoTokenActiveDTO
    {
        return $this->active;
    }

    public function getName(): AvitoTokenNameDTO
    {
        return $this->name;
    }

    public function getClient(): AvitoTokenClientDTO
    {
        return $this->client;
    }

    public function getManager(): AvitoTokenManagerDTO
    {
        return $this->manager;
    }

    public function getPercent(): AvitoTokenPercentDTO
    {
        return $this->percent;
    }

    public function getPhone(): AvitoTokenPhoneDTO
    {
        return $this->phone;
    }

    public function getSecret(): AvitoTokenSecretDTO
    {
        return $this->secret;
    }

    public function getUser(): AvitoTokenUserDTO
    {
        return $this->user;
    }

    public function getAddress(): AvitoTokenAddressDTO
    {
        return $this->address;
    }

    /**
     * @return ArrayCollection<int, AvitoTokenKitDTO>
     */
    public function getKit(): ArrayCollection
    {
        return $this->kit;
    }

    public function addKit(AvitoTokenKitDTO $kit): void
    {
        $this->kit->add($kit);
    }

    public function removeKit(AvitoTokenKitDTO $kit): void
    {
        $this->kit->removeElement($kit);
    }


    public function setName(AvitoTokenNameDTO $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setActive(AvitoTokenActiveDTO $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function setClient(AvitoTokenClientDTO $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function setManager(AvitoTokenManagerDTO $manager): self
    {
        $this->manager = $manager;
        return $this;
    }

    public function setPercent(AvitoTokenPercentDTO $percent): self
    {
        $this->percent = $percent;
        return $this;
    }

    public function setPhone(AvitoTokenPhoneDTO $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function setSecret(AvitoTokenSecretDTO $secret): self
    {
        $this->secret = $secret;
        return $this;
    }

    public function setUser(AvitoTokenUserDTO $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function setAddress(AvitoTokenAddressDTO $address): self
    {
        $this->address = $address;
        return $this;
    }
}
