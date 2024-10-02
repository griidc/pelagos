<?php

namespace App\Controller\UI;

use App\Entity\Account;
use App\Entity\DigitalResourceTypeDescriptor;
use App\Entity\Funder;
use App\Entity\InformationProduct;
use App\Entity\ProductTypeDescriptor;
use App\Entity\ResearchGroup;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Information Product UI Controller.
 */
class InformationProductController extends AbstractController
{
    /**
     * The information product page.
     *
     * @Route("/information-product", name="pelagos_app_ui_information_product")
     */
    #[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
    public function index(SerializerInterface $serializer, EntityManagerInterface $entityManager): Response
    {
        $researchGroupList = [];
        $researchGroups = $entityManager->getRepository(ResearchGroup::class)->findAll();
        $productTypeDescriptors = $entityManager->getRepository(ProductTypeDescriptor::class)->findAll();
        $digitalResourceTypeDescriptors = $entityManager->getRepository(DigitalResourceTypeDescriptor::class)->findAll();
        $funders = $entityManager->getRepository(Funder::class)->findAll();
        $context = SerializationContext::create();
        $context->enableMaxDepthChecks();
        $context->setSerializeNull(true);
        foreach ($researchGroups as $researchGroup) {
            $researchGroupList[] = [
                'id' => $researchGroup->getId(),
                'name' => $researchGroup->getName(),
                'shortName' => $researchGroup->getShortName(),
            ];
        }

        return $this->render(
            'InformationProduct/index.html.twig',
            [
                'researchGroups' => $researchGroupList,
                'productTypeDescriptors' => $serializer->serialize($productTypeDescriptors, 'json', $context),
                'digitalResourceTypeDescriptors' => $serializer->serialize($digitalResourceTypeDescriptors, 'json'),
                'funders' => $serializer->serialize($funders, 'json'),
            ]
        );
    }

    /**
     * The information product page.
     *
     * @Route("/information-product/{id}", name="pelagos_app_ui_edit_information_product", requirements={"id"="\d+"})
     */
    #[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
    public function edit(InformationProduct $informationProduct, SerializerInterface $serializer, EntityManagerInterface $entityManager): Response
    {
        $context = SerializationContext::create();
        $context->enableMaxDepthChecks();
        $context->setSerializeNull(true);
        $researchGroupList = [];
        $researchGroups = $entityManager->getRepository(ResearchGroup::class)->findAll();
        $productTypeDescriptors = $entityManager->getRepository(ProductTypeDescriptor::class)->findAll();
        $digitalResourceTypeDescriptors = $entityManager->getRepository(DigitalResourceTypeDescriptor::class)->findAll();
        $funders = $entityManager->getRepository(Funder::class)->findAll();
        foreach ($researchGroups as $researchGroup) {
            $researchGroupList[] = [
                'id' => $researchGroup->getId(),
                'name' => $researchGroup->getName(),
                'shortName' => $researchGroup->getShortName(),
            ];
        }

        return $this->render(
            'InformationProduct/edit.html.twig',
            [
                'researchGroups' => $researchGroupList,
                'informationProduct' => $serializer->serialize($informationProduct, 'json', $context),
                'productTypeDescriptors' => $serializer->serialize($productTypeDescriptors, 'json'),
                'digitalResourceTypeDescriptors' => $serializer->serialize($digitalResourceTypeDescriptors, 'json'),
                'funders' => $serializer->serialize($funders, 'json'),
            ]
        );
    }

    /**
     * The information product page.
     *
     * @Route("/information-products", name="pelagos_app_ui_information_products")
     */
    #[IsGranted(Account::ROLE_DATA_REPOSITORY_MANAGER)]
    public function list(): Response
    {
        $researchGroupList = [];
        $researchGroups = $this->getDoctrine()->getRepository(ResearchGroup::class)->findAll();
        $productTypeDescriptors = $this->getDoctrine()->getRepository(ProductTypeDescriptor::class)->findAll();
        foreach ($researchGroups as $researchGroup) {
            $researchGroupList[] = [
                'id' => $researchGroup->getId(),
                'name' => $researchGroup->getName(),
                'shortName' => $researchGroup->getShortName(),
            ];
        }

        return $this->render(
            'InformationProduct/list.html.twig',
            ['researchGroups' => $researchGroupList, 'productTypeDescriptors' => $productTypeDescriptors]
        );
    }

    /**
     * Landing page for information product.
     *
     * @Route("/infoprod/{id}", name="pelagos_app_ui_info_product_landing", requirements={"id"="\d+"})
     */
    public function infoProductLanding(InformationProduct $informationProduct): Response
    {
        return $this->render(
            'InformationProduct/landing/index.html.twig',
            [
                'informationProduct' => $informationProduct,
            ]
        );
    }
}
