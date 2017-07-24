<?php
namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Mapping as ORM;

use Pelagos\Bundle\AppBundle\Security\EntityProperty;

use Pelagos\Bundle\AppBundle\Form\DataRepositoryType;
use Pelagos\Bundle\AppBundle\Form\PersonDataRepositoryType;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Doctrine\ORM\Query;
use Pelagos\Entity\ResearchGroup;

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


        $allResearchGroups = $this->get('pelagos.entity.handler')->getAll(ResearchGroup::class,array('name' => 'ASC'));
        $data = '';
        foreach ($allResearchGroups as $rg) {

            $datasets = $rg->getDatasets();
            $dsCount = count($datasets);
            $str = '<h3>' . $rg->getName() . '</h3> ';
            $data .= $str;
            $str = '<h4>' . $dsCount . ' data sets' . '</h4> ' ;
            $data .= $str;
            $str = '<ol>';
            foreach($datasets as $ds) {
                $str .= '<li>' . 'dataset - udi: ' . $ds->getUdi(). ' - ' . $ds->getTitle() . '<br>';
                $dif  = $ds->getDif();
                $str .= 'dif status: ' . $dif->getStatusString() . '<br>';
                $str .= 'dif title: ' . $dif->getTitle(). '<br>';
                $ppoc = $dif->getPrimaryPointOfContact();
                $str .= 'dif PPOC: ' .  $ppoc->getLastName(). ', ' . $ppoc->getFirstName() . '  - ' . $ppoc->getEmailAddress() . '<br>';
                $str .= '</li>';
            }
            $str .= '</ol>';
            $data .= $str;
        }

        return new Response(
            $data,
            Response::HTTP_OK,
            array('content-type' => 'text/html')
        );
    }
}