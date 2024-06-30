<?php

declare(strict_types=1);

namespace BaksDev\Avito\Type\Event;

use BaksDev\Core\Type\UidType\Uid;

final class AvitoTokenEventUid extends Uid
{
    /** Тестовый идентификатор */
    public const string TEST = '5d8bfeba-a2e7-4886-ae98-5ec326cc516a';

    public const string TYPE = 'avito_token_event';

}