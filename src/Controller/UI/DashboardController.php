<?php

namespace App\Controller\UI;

use App\Util\PersonUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_ui_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $person = PersonUtil::getPersonFromUser($this->getUser());
        $researchGroups = $person?->getResearchGroups();

        return $this->render('Dashboard/index.html.twig', [
            'person' => $person,
            'researchGroups' => $researchGroups,
        ]);
    }
}
