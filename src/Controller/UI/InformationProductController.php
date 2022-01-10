<?php

namespace App\Controller\UI;

use App\Entity\ResearchGroup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Information Product UI Controller
 */
class InformationProductController extends AbstractController
{
    /**
     * The information product page.
     *
     * @Route("/information-product", name="pelagos_app_ui_information_product")
     *
     * @return Response A Response instance.
     */
    public function index(): Response
    {
        $researchGroupList = [];
        $researchGroups = $this->getDoctrine()->getRepository(ResearchGroup::class)->findAll();
        foreach ($researchGroups as $researchGroup) {
            $researchGroupList[] = array(
                'id' => $researchGroup->getId(),
                'name' => $researchGroup->getName(),
            );
        }
        return $this->render('InformationProduct/index.html.twig', array('researchGroups' => $researchGroupList));
    }
}
