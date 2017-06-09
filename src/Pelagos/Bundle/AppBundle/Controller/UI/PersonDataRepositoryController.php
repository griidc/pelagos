<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Pelagos\Bundle\AppBundle\Form\PersonDataRepositoryType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Research Group controller for the Pelagos UI App Bundle.
 */
class PersonDataRepositoryController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * The Person Research Group action.
     *
     * @param string $id The id of the entity to retrieve.
     *
     * @Route("/person-data-repository/{id}")
     *
     * @return Response A Response instance.
     */
    public function defaultAction($id = null)
    {
        $ui = array();

        if (isset($id)) {
            $personDataRepository = $this->entityHandler->get('Pelagos:PersonDataRepository', $id);
        } else {
            $personDataRepository = new \Pelagos\Entity\PersonDataRepository;
        }

        $form = $this->get('form.factory')->createNamed(null, PersonDataRepositoryType::class, $personDataRepository);

        $ui['PersonDataRepository'] = $personDataRepository;
        $ui['form'] = $form->createView();
        $ui['entityService'] = $this->entityHandler;

        return $this->render('PelagosAppBundle:template:PersonDataRepository.html.twig', $ui);
    }
}
