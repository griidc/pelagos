<?php

namespace App\Controller\UI;

use App\Entity\DataRepository;
use App\Form\DataRepositoryType;
use App\Form\PersonDataRepositoryType;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @throws NotFoundException When the Funding Organization is not found.
     *
     * @Route("/data-repository/{id}", name="pelagos_app_ui_datarepository_default")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(int $id)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $ui = array();

        if ($id !== null) {
            $dataRepository = $this->getDoctrine()->getRepository(DataRepository::class)->find($id);

            if (!$dataRepository instanceof DataRepository ) {
                throw $this->createNotFoundException('The Data Repository was not found');
            }

            foreach ($dataRepository->getPersonDataRepositories() as $personDataRepository) {
                $formView = $this
                    ->get('form.factory')
                    ->createNamed(null, PersonDataRepositoryType::class, $personDataRepository)
                    ->createView();

                $ui['PersonDataRepositories'][] = $personDataRepository;
                $ui['PersonDataRepositoryForms'][$personDataRepository->getId()] = $formView;
            }
        } else {
            $dataRepository = new DataRepository;
        }

        $form = $this->get('form.factory')->createNamed(null, DataRepositoryType::class, $dataRepository);

        $ui['DataRepository'] = $dataRepository;
        $ui['form'] = $form->createView();

        return $this->render('template/DataRepository.html.twig', $ui);
    }
}
