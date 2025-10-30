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

namespace BaksDev\Avito\Controller\Admin;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Type\Event\AvitoTokenEventUid;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditForm;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_AVITO_TOKEN_EDIT')]
final class EditController extends AbstractController
{
    #[Route('/admin/avito/token/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity] AvitoTokenEvent $AvitoTokenEvent,
        AvitoTokenNewEditHandler $AvitoTokenNewEditHandler
    ): Response
    {
        $AvitoTokenNewEditDTO = new AvitoTokenNewEditDTO();

        /** Запрещаем редактировать чужой токен */
        if($this->isAdmin() === true || $AvitoTokenEvent->getProfile()->equals($this->getProfileUid()) === true)
        {
            $AvitoTokenEvent->getDto($AvitoTokenNewEditDTO);
        }

        if($request->getMethod() === 'GET')
        {
            $AvitoTokenNewEditDTO
                ->getSecret()
                ->hiddenSecret();
        }

        $form = $this
            ->createForm(
                type: AvitoTokenNewEditForm::class,
                data: $AvitoTokenNewEditDTO,
                options: ['action' => $this->generateUrl('avito:admin.newedit.edit', ['id' => $AvitoTokenNewEditDTO->getEvent() ?: new AvitoTokenEventUid()]),],
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('avito_token'))
        {
            $this->refreshTokenForm($form);

            /** Запрещаем редактировать чужой токен */
            if($this->isAdmin() === false && $this->getProfileUid()?->equals($AvitoTokenNewEditDTO->getProfile()->getValue()) !== true)
            {
                $this->addFlash('breadcrumb.edit', 'danger.edit', 'avito.admin', '404');
                return $this->redirectToReferer();
            }

            $avitoToken = $AvitoTokenNewEditHandler->handle($AvitoTokenNewEditDTO);

            if($avitoToken instanceof AvitoToken)
            {
                $this->addFlash('breadcrumb.edit', 'success.edit', 'avito.admin');

                return $this->redirectToRoute('avito:admin.index');
            }

            $this->addFlash('breadcrumb.edit', 'danger.edit', 'avito.admin', $avitoToken);

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}
