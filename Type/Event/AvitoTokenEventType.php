<?php

declare(strict_types=1);

namespace BaksDev\Avito\Type\Event;

use BaksDev\Core\Type\UidType\UidType;

final class AvitoTokenEventType extends UidType
{
    public function getClassType(): string
    {
        return AvitoTokenEventUid::class;
    }

    public function getName(): string
    {
        return AvitoTokenEventUid::TYPE;
    }
}
