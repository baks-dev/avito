<?php

namespace BaksDev\Avito\Messenger;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 100)]
class AvitoTokenNullHandler
{
    // реагирую на все сообщения об изменении сущности AvitoToken
    public function __invoke(AvitoTokenMessage $message): void {}
}
