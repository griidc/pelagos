<?php

namespace App\Controller\UI;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;

use App\Form\DIFType;

use App\Entity\Account;
use App\Entity\DIF;

/**
 * The DIF controller for the Pelagos UI App Bundle.
 */
class DIFController extends AbstractController
{
    /**
     * The default action for the DIF.
     *
     * @param Request              $request     The Symfony request object.
     * @param FormFactoryInterface $formFactory The form factory.
     * @param string|null          $id          The id of the DIF to load.
     *
     * @Route("/dif/{id}", name="pelagos_app_ui_dif_default")
     *
     * @return Response A Response instance.
     */
    public function index(Request $request, FormFactoryInterface $formFactory, $id = null)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $dif = new DIF;
        $form = $formFactory->createNamed(null, DIFType::class, $dif);

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
            'DIF/dif.html.twig',
            array(
                'form' => $form->createView(),
                'research_groups' => implode(',', $researchGroupIds),
            )
        );
    }
}
