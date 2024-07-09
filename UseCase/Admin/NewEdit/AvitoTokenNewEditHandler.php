<?php

declare(strict_types=1);

namespace BaksDev\Avito\UseCase\Admin\NewEdit;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Messenger\AvitoTokenMessage;
use BaksDev\Core\Entity\AbstractHandler;
use Doctrine\DBAL\Driver\PDO\Exception;
use Psr\Log\LoggerInterface;

final class AvitoTokenNewEditHandler extends AbstractHandler
{
    public function handle(AvitoTokenNewEditDTO $newEditDTO): string|AvitoToken
    {
        $this->validatorCollection->add($newEditDTO);

        $this->main = new AvitoToken($newEditDTO->getProfile());
        $this->event = new AvitoTokenEvent();

        try
        {
            // если события нет, выполняем persist, если есть - update
            $newEditDTO->getEvent() ? $this->preUpdate($newEditDTO) : $this->prePersist($newEditDTO);
        }
        catch (\DomainException $exception)
        {
            return $exception->getMessage();
        }

        /** Валидация всех объектов */
        if ($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        try
        {
            $this->entityManager->flush();
        }
        catch (\Exception $exception)
        {
            // @todo почему не ошибка не пишется в лог
            return $exception->getMessage();
        }

        $this->messageDispatch->dispatch(
            message: new AvitoTokenMessage($this->main->getId(), $this->main->getEvent(), $newEditDTO->getEvent()),
            transport: 'avito'
        );

        return $this->main;
    }

}
