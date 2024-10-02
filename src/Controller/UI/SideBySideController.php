<?php

namespace App\Controller\UI;

use App\Handler\EntityHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Form\DatasetSubmissionType;
use App\Entity\DatasetSubmission;
use App\Entity\Dataset;
use App\Entity\DatasetLink;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * The DIF controller for the Pelagos UI App Bundle.
 */
class SideBySideController extends AbstractController
{
    /**
     * Valid values for $datasetFileTransferType and $metadataFileTransferType.
     */
    const SUBMISSIONS_STATES = array(
        DatasetSubmission::STATUS_UNSUBMITTED => 'Unsubmitted',
        DatasetSubmission::STATUS_INCOMPLETE => 'Draft',
        DatasetSubmission::STATUS_COMPLETE => 'Submitted',
        DatasetSubmission::STATUS_IN_REVIEW => 'In Review',
    );

    /**
     * An entity handler instance.
     *
     * @var EntityHandler
     */
    protected $entityHandler;

    /**
     * SideBySideController constructor.
     *
     * @param EntityHandler $entityHandler An entity handler instance.
     */
    public function __construct(EntityHandler $entityHandler)
    {
        $this->entityHandler = $entityHandler;
    }

    /**
     * The default action for Side by Side.
     *
     * @param Request     $request The Symfony request object.
     * @param string|null $udi     The UDI of the Dataset to load.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/sidebyside/{udi}', name: 'pelagos_app_ui_sidebyside_default', methods: ['GET'])]
    public function defaultAction(Request $request, $udi = null)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect('/user/login?destination=' . $request->getPathInfo());
        }

        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER') and !$this->isGranted('ROLE_SUBJECT_MATTER_EXPERT')) {
            return $this->render('template/AdminOnly.html.twig');
        }

        return $this->render(
            'SideBySide/index.html.twig'
        );
    }

    /**
     * The get versions action for Side by Side.
     *
     * @param Request $request The Symfony request object.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/sidebyside', name: 'pelagos_app_ui_sidebyside_getversions', methods: ['POST'])]
    public function getVersions(Request $request)
    {
        $udi = $request->request->get('udi');

        try {
            $datasetSubmissionHistory = $this->getDatasetSubmissionHistory($udi);
        } catch (\Exception $e) {
            return new JsonResponse(
                null,
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $submissions = array();

        foreach ($datasetSubmissionHistory->getIterator() as $i => $submission) {
            $data = array();
            $data['version'] = $i;
            $data['udi'] = $submission->getDataset()->getUdi();
            $data['sequence'] = $submission->getSequence();
            $data['status'] = self::SUBMISSIONS_STATES[$submission->getStatus()];
            $data['modifier'] = $submission->getModifier()->getLastName() .
                ', ' . $submission->getModifier()->getFirstName();
            $data['modificationtimestamp'] = $submission->getModificationTimeStamp()->format('c');
            $submissions[] = $data;
        }

        $dataset = $datasetSubmissionHistory->first()->getDataset();
        $submissions['datasetstatus'] = Dataset::DATASET_STATUSES[$dataset->getDatasetStatus()];
        $submissions['udi'] = $dataset->getUdi();

        return new JsonResponse(
            $submissions,
            JsonResponse::HTTP_OK
        );
    }

    /**
     * The get submission form action for the Side By Side controller.
     *
     * @param Request     $request  The Symfony request object.
     * @param string|null $udi      The UDI of the Dataset to load.
     * @param string|null $revision The revision number of the Submission to load.
     *
     * @throws \Exception If revision does not exists.
     *
     *
     * @return Response A Response instance.
     */
    #[Route(path: '/sidebyside/getForm/{udi}/{revision} ', name: 'pelagos_app_ui_sidebyside_getsubmissionform')]
    public function getSubmissionFormAction(Request $request, FormFactoryInterface $formFactory, $udi = null, $revision = null)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirect('/user/login?destination=' . $request->getPathInfo());
        }

        try {
            $datasetSubmissionHistory = $this->getDatasetSubmissionHistory($udi);

            if ($datasetSubmissionHistory->count() < $revision and $revision !== null) {
                throw new \Exception("Revision $revision does not exist for UDI: $udi");
            }
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
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

        $form = $formFactory->createNamed('', DatasetSubmissionType::class, $datasetSubmission);

        //Overwrite the spatial extent field which is normally a hidden type.
        $form->add('spatialExtent', TextareaType::class, array(
            'label' => 'Spatial Extent GML',
            'required' => false,
            'attr' => array(
                'rows' => '10',
                'readonly' => 'true'
            ),
        ));

        // Add file name, hash and filesize.
        $form->add('datasetFileName', TextType::class, array(
            'label' => 'Dataset File Name',
            'required' => false,
            'attr' => array(
                'readonly' => 'true'
            ),
        ));

        $form->add('datasetFileSha256Hash', TextType::class, array(
            'label' => 'Dataset SHA256 hash',
            'required' => false,
            'attr' => array(
                'readonly' => 'true'
            ),
        ));

        $form->add('datasetFileSize', TextType::class, array(
            'label' => 'Dataset Filesize',
            'required' => false,
            'attr' => array(
                'readonly' => 'true'
            ),
        ));

        return $this->render(
            'SideBySide/submissionForm.html.twig',
            array(
                'form' => $form->createView(),
                'datasetSubmission' => $datasetSubmission,
                'showForceImport' => false,
                'showForceDownload' => false,
                'researchGroupList' => $researchGroupList,
                'mode' => 'view',
                'linkoptions' => DatasetLink::getLinkNameCodeChoices(),
            )
        );
    }

    /**
     * Get the dataset submission history from UDI.
     *
     * @param string|null $udi The UDI of the Dataset to load.
     *
     * @throws \Exception If dataset if not found.
     * @throws \Exception If more than one dataset is returned.
     *
     * @return DatasetSubmissionHistory An array collection of submissions.
     */
    private function getDatasetSubmissionHistory($udi)
    {
        $datasets = $this->entityHandler->getBy(Dataset::class, array('udi' => $udi));

        if (count($datasets) == 0) {
            throw new \Exception("No dataset found for UDI: $udi");
        }

        if (count($datasets) > 1) {
            throw new \Exception("Got more than one return for UDI: $udi");
        }

        $dataset = $datasets[0];

        return $dataset->getDatasetSubmissionHistory();
    }
}
