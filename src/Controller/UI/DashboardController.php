<?php

namespace App\Controller\UI;

use App\Repository\DatasetRepository;
use App\Repository\PersonResearchGroupRepository;
use App\Repository\ResearchGroupRepository;
use App\Util\PersonUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_ui_dashboard')]
    #[IsGranted('ROLE_USER')]
    public function index(DatasetRepository $datasetRepository): Response
    {
        $person = PersonUtil::getPersonFromUser($this->getUser());

        $datasets = $datasetRepository->findBy(['creator' => $person]);

        $researchGroups = new ArrayCollection();
        foreach ($datasets as $dataset) {
            $researchGroup = $dataset->getResearchGroup();
            if (!$researchGroups->contains($researchGroup)) {
                $researchGroups->add($researchGroup);
            }
        }

        $researchGroups = $researchGroups->toArray();

        return $this->render('Dashboard/index.html.twig', [
            'person' => $person,
            'researchGroups' => $researchGroups,
        ]);
    }
}
