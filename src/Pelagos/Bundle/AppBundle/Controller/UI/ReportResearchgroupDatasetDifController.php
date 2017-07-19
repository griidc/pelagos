<?php
namespace Pelagos\Bundle\AppBundle\Controller\UI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
/**
 * The DIF controller for the Pelagos UI App Bundle.
 *
 * @Route("report-researchgroup-dataset-dif")
 */
class ReportResearchgroupDatasetDifController extends UIController implements OptionalReadOnlyInterface
{
    /**
     * The default action for the DIF.
     *
     * @param Request     $request The Symfony request object.
     *
     * @Route("")
     *
     * @return Response A Response instance.
     */
    public function defaultAction(Request $request)
    {



        $entityHandler = $this->get('pelagos.entity.handler');
         $allResearchGroups = $entityHandler->getAll(
             ResearchGroup::class,
             Query::HYDRATE_ARRAY
         );
        $data = array();
        foreach ($allResearchGroups as $researchGroup) {
            $dataset = $datasetPublication['dataset'];
            $linkId = $datasetPublication['id'];
            $fc = $dataset['researchGroup']['fundingCycle']['name'];
            $proj = $dataset['researchGroup']['name'];
            $udi = $dataset['udi'];
            $doi = $datasetPublication['publication']['doi'];
            $linkCreator = $datasetPublication['creator']['firstName'] .
                ' ' . $datasetPublication['creator']['lastName'];
            $createdOn = $datasetPublication['creationTimeStamp']->
                setTimezone(new \DateTimeZone('America/Chicago'))->format('m/d/y H:i:s') . ' CDT';
            $data[] = array(
                'id' => $linkId,
                'fc' => $fc,
                'proj' => $proj,
                'udi' => $udi,
                'doi' => $doi,
                'username' => $linkCreator,
                'created' => $createdOn
            );
        }
        // $data;
        return new Response(
            'Hello world from ReportResearchGroupDatasetDifController!',
            Response::HTTP_OK,
            array('content-type' => 'text/html')
        );
    }
}