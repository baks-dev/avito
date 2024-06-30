<?php

namespace BaksDev\Avito\Messenger;

use BaksDev\Avito\Messenger\AvitoTokenMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 100)]
class AvitoTokenDispatch
{
    // реагирую на все сообщения об изменении сущности AvitoToken
    public function __invoke(AvitoTokenMessage $message): void {}
}