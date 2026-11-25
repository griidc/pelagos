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

    #[Route('/dashboard-test-banners', name: 'app_ui_dashboard_test_banners')]
    #[IsGranted('ROLE_USER')]
    public function testBanners(): Response
    {
        $person = PersonUtil::getPersonFromUser($this->getUser());
        $researchGroups = $person?->getResearchGroups();
        // Additional logic for test banners can be added here if needed.
        $successMessage = 'This is a test success banner message.';
        $this->addFlash('success', $successMessage);

        $errorMessage = 'This is a test warning banner message.';
        $this->addFlash('warning', $errorMessage);

        $infoMessage = 'This is a test error banner message.';
        $this->addFlash('error', $infoMessage);

        $warningMessage = 'This is a test default banner message.';
        $this->addFlash('default', $warningMessage);

        return $this->render('Dashboard/index.html.twig', [
            'person' => $person,
            'researchGroups' => $researchGroups,
        ]);
    }

}
