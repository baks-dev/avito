<?php

declare(strict_types=1);

namespace BaksDev\Avito\Controller\Admin;

use BaksDev\Avito\Repository\AllAvitoToken\AllAvitoTokenInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_AVITO_TOKEN_INDEX')]
final class IndexController extends AbstractController
{
    #[Route('/admin/avito/tokens/{page<\d+>}', name: 'admin.index', methods: ['GET', 'POST'])]
    public function index(Request $request, AllAvitoTokenInterface $paginator, int $page = 0): Response
    {
        $search = new SearchDTO();
        $searchForm = $this->createForm(
            SearchForm::class,
            $search,
            ['action' => $this->generateUrl('avito:admin.index')],
        );

        $searchForm->handleRequest($request);

        $admin = $this->getAdminFilterProfile();

        if(null !== $admin)
        {
            $paginator->profile($admin);
        }

        $avitoTokens = $paginator
            ->search($search)
            ->findAll();

        return $this->render(
            [
                'query' => $avitoTokens,
                'search' => $searchForm->createView(),
            ],
        );
    }
}
