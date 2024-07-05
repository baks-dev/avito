<?php

declare(strict_types=1);

namespace BaksDev\Avito\UseCase\Admin\Delete;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Messenger\AvitoTokenMessage;
use BaksDev\Core\Entity\AbstractHandler;

final class AvitoTokenDeleteHandler extends AbstractHandler
{
    /** @see AvitoToken */
    public function handle(AvitoTokenDeleteDTO $dto): string|AvitoToken
    {

        /** Валидация DTO  */
        $this->validatorCollection->add($dto);

        $this->main = new AvitoToken($dto->getProfile());
        $this->event = new AvitoTokenEvent();

        try
        {
            $this->preRemove($dto);
        }
        catch (\DomainException $errorUniqid)
        {
            return $errorUniqid->getMessage();
        }

        /** Валидация всех объектов */
        if ($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        // @todo AbstractHandler в методе preRemove не присваивается в корень агрегата информация об событии удаления - баг или фича?
        // dump($dto->getEvent());
        // dump($this->event->getId());

        $this->entityManager->flush();

        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new AvitoTokenMessage($this->main->getId(), $this->main->getEvent(), $dto->getEvent()),
            transport: 'avito'
        );

        return $this->main;
    }
}
