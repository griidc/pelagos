<?php

namespace App\Controller\UI;

use App\Entity\DataRepository;
use App\Form\DataRepositoryType;
use App\Form\PersonDataRepositoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class DataRepositoryController extends AbstractController
{
    /**
     * The Funding Org action.
     *
     * @param integer $id The id of the entity to retrieve.
     *
     * @throws NotFoundHttpException When the Funding Organization is not found.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/data-repository/{id}', name: 'pelagos_app_ui_datarepository_default')]
    public function defaultAction(int $id, EntityManagerInterface $entityManager, FormFactoryInterface $formFactory)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $ui = array();

        if ($id !== null) {
            $dataRepository = $entityManager->getRepository(DataRepository::class)->find($id);

            if (!$dataRepository instanceof DataRepository) {
                throw new NotFoundHttpException('The Data Repository was not found');
            }

            foreach ($dataRepository->getPersonDataRepositories() as $personDataRepository) {
                $formView = $formFactory
                    ->createNamed('', PersonDataRepositoryType::class, $personDataRepository)
                    ->createView();

                $ui['PersonDataRepositories'][] = $personDataRepository;
                $ui['PersonDataRepositoryForms'][$personDataRepository->getId()] = $formView;
            }
        } else {
            $dataRepository = new DataRepository();
        }

        $form = $formFactory->createNamed('', DataRepositoryType::class, $dataRepository);

        $ui['DataRepository'] = $dataRepository;
        $ui['form'] = $form->createView();

        return $this->render('template/DataRepository.html.twig', $ui);
    }
}
