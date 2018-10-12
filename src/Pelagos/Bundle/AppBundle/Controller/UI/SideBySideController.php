<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Bundle\AppBundle\Form\DatasetSubmissionType;

use Pelagos\Entity\Account;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\Dataset;
use Pelagos\Entity\DIF;
use Pelagos\Entity\PersonDatasetSubmissionDatasetContact;

use Pelagos\Response\TerminateResponse;
 

/**
 * The DIF controller for the Pelagos UI App Bundle.
 *
 * @Route("/sidebyside")
 */
class SideBySideController extends UIController
{
    /**
     * The default action for the DIF.
     *
     * @param Request     $request The Symfony request object.
     * @param string|null $id      The id of the DIF to load.
     *
     * @Route("/")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect('/user/login?destination=' . $request->getPathInfo());
        }

        return $this->render(
            'PelagosAppBundle:SideBySide:index.html.twig',
            array(
                'udilist' => 'test',
                
            )
        );
    }
    
    /**
     * The get submission form action for the Side By Side controller.
     *
     * @param Request     $request  The Symfony request object.
     * @param string|null $udi      The UDI of the Dataset to load.
     * @param string|null $revision The revision number of the Submission to load.
     *
     * @Route("/getForm/{udi}/{revision}")
     *
     * @return Response A Response instance.
     */
    public function getSubmissionFormAction(Request $request, $udi = null, $revision = null)
    {
        // if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // return $this->redirect('/user/login?destination=' . $request->getPathInfo());
        // }
        
        $datasets = $this->entityHandler->getBy(Dataset::class, array('udi' => $udi));

        if (count($datasets) == 0) {
            throw $this->createNotFoundException("No dataset found for UDI: $udi");
        }

        if (count($datasets) > 1) {
            throw new \Exception("Got more than one return for UDI: $udi");
        }

        $dataset = $datasets[0];

        $datasetSubmissionHistory = $dataset->getDatasetSubmissionHistory();
        
        if ($datasetSubmissionHistory->count() <= $revision and $revision !== null) {
            throw new \Exception("Revision $revision does not exist for UDI: $udi");
        }
        
        if ($revision !== null) {
            $datasetSubmission = $datasetSubmissionHistory[$revision];
        } else {
            $datasetSubmission = $datasetSubmissionHistory->first();
        }
        
        $researchGroupList = array();
        $account = $this->getUser();
        if (null !== $account) {
            $user = $account->getPerson();
            // Find all RG's user has CREATE_DIF_DIF_ON on.
            $researchGroups = $user->getResearchGroups();
            $researchGroupList = array_map(
                function ($researchGroup) {
                    return $researchGroup->getId();
                },
                $researchGroups
            );
        }
        
        $form = $this->get('form.factory')->createNamed(null, DatasetSubmissionType::class, $datasetSubmission);
        
        $terminateResponse = new TerminateResponse();

        return $this->render(
            'PelagosAppBundle:SideBySide:submissionForm.html.twig',
            array(
                'form' => $form->createView(),
                'datasetSubmission' => $datasetSubmission,
                'showForceImport' => false,
                'showForceDownload' => false,
                'researchGroupList' => $researchGroupList,
                'mode' => 'view',
            ), 
            $terminateResponse
        );
    }
}
