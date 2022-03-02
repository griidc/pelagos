<?php

namespace App\Controller\UI;

use App\Entity\InformationProduct;
use App\Entity\ResearchGroup;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * @IsGranted("ROLE_DATA_REPOSITORY_MANAGER")
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
                'shortName' => $researchGroup->getShortName(),
            );
        }
        return $this->render('InformationProduct/index.html.twig', array('researchGroups' => $researchGroupList));
    }

    /**
     * The information product page.
     *
     * @Route("/information-product/{id}", name="pelagos_app_ui_edit_information_product", requirements={"id"="\d+"})
     *
     * @IsGranted("ROLE_DATA_REPOSITORY_MANAGER")
     *
     * @return Response A Response instance.
     */
    public function edit(InformationProduct $informationProduct, SerializerInterface $serializer): Response
    {
        $context = SerializationContext::create();
        $context->enableMaxDepthChecks();
        $context->setSerializeNull(true);
        $researchGroupList = [];
        $researchGroups = $this->getDoctrine()->getRepository(ResearchGroup::class)->findAll();
        foreach ($researchGroups as $researchGroup) {
            $researchGroupList[] = array(
                'id' => $researchGroup->getId(),
                'name' => $researchGroup->getName(),
                'shortName' => $researchGroup->getShortName(),
            );
        }
        return $this->render(
            'InformationProduct/edit.html.twig',
            array(
                'researchGroups' => $researchGroupList,
                'informationProduct' => $serializer->serialize($informationProduct, 'json', $context),
            )
        );
    }

    /**
     * The information product page.
     *
     * @Route("/information-products", name="pelagos_app_ui_information_products")
     *
     * @IsGranted("ROLE_DATA_REPOSITORY_MANAGER")
     *
     * @return Response A Response instance.
     */
    public function list(): Response
    {
        $researchGroupList = [];
        $researchGroups = $this->getDoctrine()->getRepository(ResearchGroup::class)->findAll();
        foreach ($researchGroups as $researchGroup) {
            $researchGroupList[] = array(
                'id' => $researchGroup->getId(),
                'name' => $researchGroup->getName(),
                'shortName' => $researchGroup->getShortName(),
            );
        }
        return $this->render('InformationProduct/list.html.twig', array('researchGroups' => $researchGroupList));
    }
}
