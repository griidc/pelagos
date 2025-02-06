<?php

namespace App\Controller\UI;

use App\Entity\PersonDataRepository;
use App\Form\PersonDataRepositoryType;
use App\Handler\EntityHandler;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class PersonDataRepositoryController extends AbstractController
{
    /**
     * The Person Research Group action.
     *
     * @param EntityHandler $entityHandler The entity handler.
     * @param integer       $id            The id of the entity to retrieve.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/person-data-repository/{id}', name: 'pelagos_app_ui_persondatarepository_default')]
    public function defaultAction(EntityHandler $entityHandler, FormFactoryInterface $formFactory, int $id = null)
    {
        // Checks authorization of users
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        $ui = array();

        if (isset($id)) {
            $personDataRepository = $entityHandler->get(PersonDataRepository::class, $id);
        } else {
            $personDataRepository = new \App\Entity\PersonDataRepository();
        }

        $form = $formFactory->createNamed('', PersonDataRepositoryType::class, $personDataRepository);

        $ui['PersonDataRepository'] = $personDataRepository;
        $ui['form'] = $form->createView();

        return $this->render('template/PersonDataRepository.html.twig', $ui);
    }
}
