<?php


declare(strict_types=1);

namespace BaksDev\Avito\Controller\Admin;

use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditForm;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_AVITO_TOKEN_NEW')]
final class NewController extends AbstractController
{
    #[Route('/admin/avito/token/new', name: 'admin.newedit.new', methods: ['GET', 'POST'])]
    public function news(Request $request, AvitoTokenNewEditHandler $newEditHandler): Response
    {
        $dto = new AvitoTokenNewEditDTO();

        if(null !== $this->getAdminFilterProfile())
        {
            $dto->setProfile($this->getProfileUid());
        }

        $form = $this->createForm(AvitoTokenNewEditForm::class, $dto, [
            'action' => $this->generateUrl('avito:admin.newedit.new'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->has('avito_token'))
        {
            $this->refreshTokenForm($form);

            $avitoToken = $newEditHandler->handle($dto);

            if($avitoToken instanceof AvitoToken)
            {
                $this->addFlash(
                    'breadcrumb.new',
                    'success.new',
                    'avito.admin'
                );

                return $this->redirectToRoute('avito:admin.index');
            }

            $this->addFlash('breadcrumb.new', 'danger.new', 'avito.admin', $avitoToken);

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}
