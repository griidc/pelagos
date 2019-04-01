<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Bundle\AppBundle\Form\DIFType;

use Pelagos\Entity\Account;
use Pelagos\Entity\DIF;

/**
 * The DIF controller for the Pelagos UI App Bundle.
 *
 * @Route("/dif")
 */
class DIFController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * The default action for the DIF.
     *
     * @param Request     $request The Symfony request object.
     * @param string|null $id      The id of the DIF to load.
     *
     * @Route("/{id}")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request, $id = null)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $dif = new DIF;
        $form = $this->get('form.factory')->createNamed(null, DIFType::class, $dif);

        $researchGroupIds = array();
        if ($this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            $researchGroupIds = array('*');
        } elseif ($this->getUser() instanceof Account) {
            $researchGroups = $this->getUser()->getPerson()->getResearchGroups();
            $researchGroupIds = array_map(
                function ($researchGroup) {
                    return $researchGroup->getId();
                },
                $researchGroups
            );
        }
        if (0 === count($researchGroupIds)) {
            $researchGroupIds = array('!*');
        }

        return $this->render(
            'PelagosAppBundle:DIF:dif.html.twig',
            array(
                'form' => $form->createView(),
                'research_groups' => implode(',', $researchGroupIds),
            )
        );
    }
}
