<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DIF;

/**
 * The GOMRI datasets report generator.
 */
class GomriReportController extends UIController
{
    /**
     * This is a parameterless report, so all is in the default action.
     *
     * @Route("/gomri")
     *
     * @return Response A Response instance.
     */
    public function defaultAction($id = null)
    {
        // get all datasets Since Sept 2016 (P.O. Decision).

        // create counts hashed by month/year.
        $data[2016][9] = 109;
        $data[2016][10] = 110;
        $data[2016][11] = 111;
        $data[2016][12] = 112;
        $data[2017][1] = 201;
        $data[2017][2] = 202;
        $data[2017][3] = 203;
        $data[2017][4] = 204;
        $data[2017][5] = 205;
        $data[2017][6] = 206;
        $data[2017][7] = 207;
        $data[2017][8] = 208;

        $reportContent = '';
        // Iterate through hashes and generate CSV.
        foreach($data as $year) {
            foreach($year as $month) {
                $reportContent .= "$month ".key($month);
            }
        }

        // Spew forth the CSV with mime/type to save as file.
        return new Response($reportContent);
    }
}
