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

namespace BaksDev\Avito\UseCase\Admin\NewEdit;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Messenger\AvitoTokenMessage;
use BaksDev\Core\Entity\AbstractHandler;
use DomainException;
use Exception;

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
        catch(DomainException $exception)
        {
            return $exception->getMessage();
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

        $this->messageDispatch
            ->addClearCacheOther('avito-board')
            ->dispatch(
            message: new AvitoTokenMessage($this->main->getId(), $this->main->getEvent(), $newEditDTO->getEvent()),
            transport: 'avito',
        );

        return $this->main;
    }

}
