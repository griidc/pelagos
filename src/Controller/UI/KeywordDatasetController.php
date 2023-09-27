<?php

namespace App\Controller\UI;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Form\DatasetSubmissionType;
use App\Form\KeywordDatasetType;
use App\Repository\DatasetRepository;
use App\Util\JsonSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route('/keyword-dataset/{udi}', name: 'pelagos_app_ui_edit_keyword_dataset', methods: 'GET')]
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

        $datasetSubmissionId = $datasetSubmission->getId();
        $url = $this->generateUrl('pelagos_app_ui_update_keyword_dataset', ['datasetSubmission' => $datasetSubmissionId]);

        $form = $formFactory->createNamed(
            '',
            DatasetSubmissionType::class,
            $datasetSubmission,
            [
                'action' => $url,
                'method' => 'PUT',
                'attr' => [
                    'datasetSubmission' => $datasetSubmissionId,
                ],
            ]
        );

        return $this->render('KeywordDataset/edit.html.twig', [
            'form' => $form->createView(),
            'dataset' => $dataset,
            'datasetSubmission' => $datasetSubmission,
        ]);
    }

    #[Route('/keyword-dataset/{datasetSubmission}', name: 'pelagos_app_ui_update_keyword_dataset', methods: 'POST')]
    public function updateDataset(
        DatasetSubmission $datasetSubmission,
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        $form = $formFactory->createNamed('', KeywordDatasetType::class, $datasetSubmission);
        $form->handleRequest($request);

        $entityManager->persist($datasetSubmission);
        $entityManager->flush();

        return new Response('OK', Response::HTTP_NO_CONTENT);
    }
}
