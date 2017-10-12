<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Security\EntityProperty;

use Pelagos\Bundle\AppBundle\Form\DataRepositoryType;
use Pelagos\Bundle\AppBundle\Form\PersonDataRepositoryType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class DataRepositoryController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * The Funding Org action.
     *
     * @param string $id The id of the entity to retrieve.
     *
     * @throws NotFoundException When the Funding Organization is not found.
     *
     * @Route("/data-repository/{id}")
     *
     * @return Response A Response instance.
     */
    public function defaultAction($id)
    {
        // Added authorization check for users to view this page
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }

        $ui = array();

        if ($id !== null) {
            $dataRepository = $this->entityHandler->get('Pelagos:DataRepository', $id);

            if (!$dataRepository instanceof \Pelagos\Entity\DataRepository) {
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
            $dataRepository = new \Pelagos\Entity\DataRepository;
        }

        $form = $this->get('form.factory')->createNamed(null, DataRepositoryType::class, $dataRepository);

        $ui['DataRepository'] = $dataRepository;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $this->entityHandler;

        return $this->render('PelagosAppBundle:template:DataRepository.html.twig', $ui);
    }
}
