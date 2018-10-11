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
     * @Route("/{udi}")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request, $udi = null)
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
        
        $forms = array();
        
        foreach($datasetSubmissionHistory->getIterator() as $i => $datasetSubmission) {
            $form = $this->get('form.factory')->createNamed(null, DatasetSubmissionType::class, $datasetSubmission);
            $forms[] = $form->createView();
        }
        
        
        
        $form = $this->get('form.factory')->createNamed(null, DatasetSubmissionType::class, null);
        $form2 = $this->get('form.factory')->createNamed(null, DatasetSubmissionType::class, null);
        

        

        return $this->render(
            'PelagosAppBundle:SideBySide:index.html.twig',
            array(
                'forms' => $forms,
                'form' => $form->createView(),
                'form2' => $form2->createView(),
            )
        );
    }
}
