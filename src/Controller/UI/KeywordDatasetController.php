<?php

namespace App\Controller\UI;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Repository\DatasetRepository;
use App\Util\JsonSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\DatasetSubmissionType;
use Symfony\Component\Form\FormFactoryInterface;

class KeywordDatasetController extends AbstractController
{
    #[Route('/keyword-dataset', name: 'pelagos_app_ui_list_keyword_dataset')]
    public function list(): Response
    {
        return $this->render('KeywordDataset/list.html.twig');
    }

    #[Route('/api/keyword_dataset', name: 'pelagos_app_api_keyword_dataset')]
    public function getList(DatasetRepository $datasetRepository, JsonSerializer $jsonSerializer): Response
    {
        $datasets = $datasetRepository->getListOfApprovedDatasetWithoutKeywords();

        return $jsonSerializer->serialize(
            data: $datasets,
        )->createJsonResponse();
    }

    #[Route('/keyword-dataset/{udi}', name: 'pelagos_app_ui_edit_keyword_dataset')]
    public function editDataset(string $udi, DatasetRepository $datasetRepository, FormFactoryInterface $formFactory): Response
    {
        $dataset = $datasetRepository->findOneBy(['udi' => $udi]);

        if (!$dataset instanceof Dataset) {
            throw new NotFoundHttpException("Dataset with $udi not found!");
        }

        $datasetSubmission = $dataset->getDatasetSubmission();

        if (!$datasetSubmission instanceof DatasetSubmission) {
            throw new NotFoundHttpException('This Dataset does not have an Submission!');
        }

        $form = $formFactory->create(DatasetSubmissionType::class, $datasetSubmission);

        return $this->render('KeywordDataset/edit.html.twig', [
            'form' => $form->createView(),
        ]);

    }
}
