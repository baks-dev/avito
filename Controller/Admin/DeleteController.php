<?php

declare(strict_types=1);

namespace BaksDev\Avito\Controller\Admin;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\UseCase\Admin\Delete\AvitoTokenDeleteDTO;
use BaksDev\Avito\UseCase\Admin\Delete\AvitoTokenDeleteForm;
use BaksDev\Avito\UseCase\Admin\Delete\AvitoTokenDeleteHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_AVITO_TOKEN_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/avito/token/delete/{id}', name: 'admin.delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity] AvitoTokenEvent $event,
        AvitoTokenDeleteHandler $deleteHandler
    ): Response
    {
        $dto = new AvitoTokenDeleteDTO();

        $event->getDto($dto);

        $form = $this->createForm(AvitoTokenDeleteForm::class, $dto, [
            'action' => $this->generateUrl('avito:admin.delete', ['id' => $dto->getEvent()]),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('avito_token_delete'))
        {
            $this->refreshTokenForm($form);

            $avitoToken = $deleteHandler->handle($dto);

            if($avitoToken instanceof AvitoToken)
            {
                $this->addFlash('breadcrumb.delete', 'success.delete', 'avito.admin');

                return $this->redirectToRoute('avito:admin.index');
            }

            $this->addFlash(
                'breadcrumb.delete',
                'danger.delete',
                'avito.admin',
                $avitoToken,
            );

            return $this->redirectToRoute('avito:admin.index', status: 400);
        }

        return $this->render([
            'form' => $form->createView(),
        ]);
    }
}
