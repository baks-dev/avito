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

namespace BaksDev\Avito\Entity\Event\Kit;

use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\UseCase\Admin\NewEdit\Kit\AvitoTokenKitDTO;
use BaksDev\Core\Entity\EntityEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see AvitoTokenKitDTO
 */
#[ORM\Entity]
#[ORM\Table(name: 'avito_token_kit')]
class AvitoTokenKit extends EntityEvent
{

    /** Связь на событие */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: AvitoTokenEvent::class, inversedBy: 'kit')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private AvitoTokenEvent $event;

    /**
     * Значение свойства
     */
    #[Assert\NotBlank]
    #[Assert\Range(min: 1)]
    #[ORM\Id]
    #[ORM\Column(type: Types::SMALLINT)]
    private int $value = 1;

    public function __construct(AvitoTokenEvent $event)
    {
        $this->event = $event;
    }

    public function __toString(): string
    {
        return (string) $this->event;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    /** @return AvitoTokenKitInterface */
    public function getDto($dto): mixed
    {

        if(is_string($dto) && class_exists($dto))
        {
            $dto = new $dto();
        }

        if($dto instanceof AvitoTokenKitInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    /** @var AvitoTokenKitInterface $dto */
    public function setEntity($dto): mixed
    {
        if($dto instanceof AvitoTokenKitInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}
