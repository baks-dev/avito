<?php

namespace BaksDev\Avito\Messenger;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 100)]
class AvitoTokenAuthHandler
{
    // реагирую на все сообщения об изменении сущности AvitoToken
    public function __invoke(AvitoTokenMessage $message): void {

        // получаю event
        // проверяю статус токена
        // обрабатываю только два экшена - new и edit
        // если
    }
}