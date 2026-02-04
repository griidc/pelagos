<?php

namespace App\Controller\UI;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use App\Form\DIFType;
use App\Entity\Account;
use App\Entity\DIF;
use App\Entity\ResearchGroup;
use App\Repository\FunderRepository;
use App\Repository\ResearchGroupRepository;
use App\Util\FundingOrgFilter;
use App\Util\PersonUtil;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * The DIF controller for the Pelagos UI App Bundle.
 */
class DIFController extends AbstractController
{
    /**
     * The default action for the DIF.
     *
     * @param Request              $request          The Symfony request object.
     * @param FormFactoryInterface $formFactory      The form factory.
     * @param FundingOrgFilter     $fundingOrgFilter Utility to filter by funding organization.
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/dif', name: 'pelagos_app_ui_dif_default')]
    public function index(Request $request, FormFactoryInterface $formFactory, FundingOrgFilter $fundingOrgFilter)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $dif = new DIF();
        $form = $formFactory->createNamed('', DIFType::class, $dif);

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

            if ($fundingOrgFilter->isActive()) {
                $filterResearchGroupsIds = $fundingOrgFilter->getResearchGroupsIdArray();
                $researchGroupIds = array_intersect($researchGroupIds, $filterResearchGroupsIds);
            }
        }


        if (0 === count($researchGroupIds)) {
            $researchGroupIds = array('!*');
        }

        return $this->render(
            'DIF/dif.v2.html.twig',
            array(
                'form' => $form->createView(),
                'research_groups' => implode(',', $researchGroupIds),
                'issueTrackingBaseUrl' => $_ENV['ISSUE_TRACKING_BASE_URL'],
            )
        );
    }

    #[Route(path: '/dif/get-research-groups', name: 'pelagos_dif_get_research_groups')]
    public function getResearchGroups(ResearchGroupRepository $researchGroupRepository): Response
    {
        $researchGroups = [];

        if ($this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            $researchGroups = $researchGroupRepository->findAll();
        } elseif ($this->getUser() instanceof Account) {
            $person = PersonUtil::getPersonFromUser($this->getUser());
            $researchGroups = $person?->getResearchGroups() ?? [];
        }

        $researchGroupsArray = array_map(function (ResearchGroup $rg) {
            return [
                'id' => $rg->getId(),
                'name' => $rg->getName(),
            ];
        }, $researchGroups);

        usort($researchGroupsArray, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return new JsonResponse(['ResearchGroups' => $researchGroupsArray]);
    }

    #[Route(path: '/dif/get-research-group-contacts/{id}', name: 'pelagos_dif_get_research_group_contacts')]
    public function getResearchGroupContacts(ResearchGroup $researchGroup): Response
    {
        $contacts = [];
        foreach ($researchGroup->getPeople() as $person) {
            $contacts[] = [
                'id' => $person->getId(),
                'name' => $person->getFullName(),
                'email' => $person->getEmailAddress(),
            ];
        }

        usort($contacts, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        return new JsonResponse(['Contacts' => $contacts]);
    }

    #[Route(path: '/dif/get-funders', name: 'pelagos_dif_get_funders')]
    public function getFunders(FunderRepository $funderRepository): Response
    {
        $funders = $funderRepository->findAll();
        $funderArray = array_map(function ($funder) {
            return [
                'id' => $funder->getId(),
                'name' => $funder->getName(),
            ];
        }, $funders);

        return new JsonResponse(['Funders' => $funderArray]);
    }
}
