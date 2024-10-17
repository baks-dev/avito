<?php

declare(strict_types=1);

namespace BaksDev\Avito\UseCase\Admin\Delete;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Messenger\AvitoTokenMessage;
use BaksDev\Core\Entity\AbstractHandler;
use DomainException;
use Exception;

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
        catch(DomainException $errorUniqid)
        {
            return $errorUniqid->getMessage();
        }

        /** Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        try
        {
            $this->entityManager->flush();
        }
        catch(Exception $exception)
        {
            return $exception->getMessage();
        }

        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new AvitoTokenMessage($this->main->getId(), $this->main->getEvent(), $dto->getEvent()),
            transport: 'avito',
        );

        return $this->main;
    }
}
