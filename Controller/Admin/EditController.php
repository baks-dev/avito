<?php

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
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_AVITO_TOKEN_EDIT')]
final class EditController extends AbstractController
{
    #[Route('/admin/avito/token/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, #[MapEntity] AvitoTokenEvent $event, AvitoTokenNewEditHandler $newEditHandler): Response
    {
        $dto = new AvitoTokenNewEditDTO();
        /** Запрещаем редактировать чужой токен */
        if($this->getAdminFilterProfile() === null || $this->getProfileUid()?->equals($event->getProfile()) === true)
        {
            $event->getDto($dto);
        }

        if($request->getMethod() === 'GET')
        {
            $dto->hiddenSecret();
        }

        $form = $this->createForm(AvitoTokenNewEditForm::class, $dto, [
            'action' => $this->generateUrl(
                'avito:admin.newedit.edit',
                ['id' => $dto->getEvent() ?: new AvitoTokenEventUid()]
            ),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('avito_token'))
        {
            $this->refreshTokenForm($form);

            /** Запрещаем редактировать чужой токен */
            if($this->getAdminFilterProfile() && $this->getAdminFilterProfile()->equals($dto->getProfile()) === false)
            {
                $this->addFlash('breadcrumb.edit', 'danger.edit', 'avito.admin', '404');
                return $this->redirectToReferer();
            }

            $avitoToken = $newEditHandler->handle($dto);

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
