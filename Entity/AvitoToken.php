<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Avito\Entity;

use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Type\Event\AvitoTokenEventUid;
use BaksDev\Avito\Type\Id\AvitoTokenUid;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'avito_token')]
class AvitoToken
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: AvitoTokenUid::TYPE)]
    private AvitoTokenUid $id;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: AvitoTokenEventUid::TYPE, unique: true, nullable: false)]
    private AvitoTokenEventUid $event;

    public function __construct()
    {
        $this->id = new AvitoTokenUid();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): AvitoTokenUid
    {
        return $this->id;
    }

    public function getEvent(): AvitoTokenEventUid
    {
        return $this->event;
    }

    public function setEvent(AvitoTokenEvent|AvitoTokenEventUid $eventId): void
    {
        $this->event = $eventId instanceof AvitoTokenEvent ? $eventId->getId() : $eventId;
    }
}
