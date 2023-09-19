<?php

namespace App\Controller\UI;

use App\Controller\Admin\DatasetSubmissionCrudController;
use App\Entity\Dataset;
use App\Repository\DatasetRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DatasetKeywordFinderController extends AbstractController
{
    #[Route('/datasetKeywordFinder', name:"pelagos_app_ui_dataset_keyword_finder")]
    public function index(DatasetRepository $datasetRepository, AdminUrlGenerator $adminUrlGenerator, Request $request): Response
    {
        $udi = $request->query->get('udi');

        if (!empty($udi)) {
            /** @var Dataset $dataset */
            $dataset = $datasetRepository->findOneBy(['udi' => $udi]);
            $datasetSubmission = $dataset->getLatestDatasetReview();

            $url = $adminUrlGenerator
            ->setController(DatasetSubmissionCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId($datasetSubmission->getId())
            ->generateUrl();

            return $this->redirect($url);
        }

        $datasets = $datasetRepository->getListOfUDIs();

        return $this->render('DatasetKeywordFinder/index.html.twig', [
            'datasets' => $datasets,
        ]);
    }
}
