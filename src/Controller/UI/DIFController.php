<?php

namespace App\Controller\UI;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use App\Form\DIFType;
use App\Entity\Account;
use App\Entity\Dataset;
use App\Entity\DIF;
use App\Entity\ResearchGroup;
use App\Repository\DatasetRepository;
use App\Repository\FunderRepository;
use App\Repository\ResearchGroupRepository;
use App\Util\FundingOrgFilter;
use App\Util\PersonUtil;
use App\Util\Udi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\SubmitButton;

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
    #[Route(path: '/dif-old', name: 'pelagos_app_ui_dif_old')]
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
            'DIF/dif.html.twig',
            array(
                'form' => $form->createView(),
                'research_groups' => implode(',', $researchGroupIds),
                'issueTrackingBaseUrl' => $_ENV['ISSUE_TRACKING_BASE_URL'],
            )
        );
    }

    #[Route(path: '/difok')]
    public function confirmTest(Request $request, DatasetRepository $datasetRepository): Response
    {
        $udi = $request->query->get('udi');
        if ($udi !== null && $udi !== '') {
            $dataset = $datasetRepository->findOneBy(['udi' => $udi]);
            if (!$dataset) {
                // add to flash bag errror message about dataset not found
                $this->addFlash('error', 'Dataset not found for UDI: ' . $udi);
            }
        }

        return $this->render('DIF/dif-confirmation.html.twig', [
            'dataset' => $dataset ?? null,
        ]);
    }

    #[Route(path: '/dif', name: 'pelagos_app_ui_dif_default')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function difTwo(Request $request, FormFactoryInterface $formFactory, DatasetRepository $datasetRepository, EntityManagerInterface $entityManager, Udi $udiUtil): Response
    {
        $dataset = null;
        $dif = null;
        $udi = $request->query->get('udi');
        if ($udi !== null && $udi !== '') {
            $dataset = $datasetRepository->findOneBy(['udi' => $udi]);
            if (!$dataset) {
                // add to flash bag errror message about dataset not found
                $this->addFlash('error', 'Dataset not found for UDI: ' . $udi);
            } else {
                $dif = $dataset->getDif();
            }
        }

        if (!$dataset instanceof Dataset) {
            $creator = PersonUtil::getPersonFromUser($this->getUser());
            $dataset = new Dataset();
            $dataset->setCreator($creator);
            $dif = new DIF($dataset);
            $dif->setCreator($creator);
        }

        $form = $formFactory->createNamed('', DIFType::class, $dif);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($dataset->getResearchGroup()->isLocked()) {
                throw new \Exception('The selected research group is locked and cannot be used. Please select a different research group.');
            }

            if ($dif->getStatus() === DIF::STATUS_SUBMITTED && !$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
                throw new \Exception('You do not have permission to save this DIF.');
            }

            if ($dataset->getUdi() === null) {
                $udiUtil->mintUdi($dataset);
            }

            /** @var SubmitButton $saveAndSubmit */
            $saveAndSubmit = $form->get('saveAndSubmit');
            $extraData = $form->getExtraData();

            // `submitAction` is populated from event.submitter.name in frontend JS.
            // Fall back to button names in extraData so the action still works without JS.
            $submitAction = $extraData['submitAction'] ?? null;
            if ($submitAction === null) {
                foreach (['saveAndSubmit', 'drpmUpdateSubmission', 'approveSubmission', 'rejectSubmission'] as $buttonName) {
                    if (isset($extraData[$buttonName])) {
                        $submitAction = $buttonName;
                        break;
                    }
                }
            }

            if (in_array($submitAction, ['drpmUpdateSubmission', 'approveSubmission', 'rejectSubmission'], true)) {
                $this->denyAccessUnlessGranted('ROLE_DATA_REPOSITORY_MANAGER');
            }

            switch ($submitAction) {
                case 'saveAndSubmit':
                    $dif->submit();
                    break;
                case 'approveSubmission':
                    $dif->approve();
                    $this->addFlash('success', 'DIF successfully approved');
                    break;
                case 'rejectSubmission':
                    $dif->unlock();
                    $this->addFlash('success', 'DIF successfully unlocked');
                    break;
                case 'drpmUpdateSubmission':
                    $this->addFlash('success', 'DIF successfully updated');
                    break;
                default:
                    break;
            }

            $entityManager->persist($dif);
            $entityManager->persist($dataset);

            $entityManager->flush();

            if ($this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
                return $this->render('DIF/drpm-dif-confirmation.html.twig', ['dataset' => $dataset,]);
            } else {
                return $this->render('DIF/dif-confirmation.html.twig', ['dataset' => $dataset,]);
            }
        }

        return $this->render(
            'DIF/dif.v2.html.twig',
            [
                'form' => $form,
                'udi' => $dataset->getUdi(),
                'status' => $dif->getStatus(),
            ]
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

    #[Route(path: '/dif/check-research-group/{id}', name: 'pelagos_dif_check_research_group')]
    public function checkResearchGroup(ResearchGroup $researchGroup): JsonResponse
    {
        return new JsonResponse(['locked' => $researchGroup->isLocked()]);
    }
}
