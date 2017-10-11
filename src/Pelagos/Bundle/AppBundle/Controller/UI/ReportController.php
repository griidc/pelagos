<?php
namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\StreamedResponse;
use \DateTime;

/**
 * This abstract controller provide a template for reports.
 *
 * @package Pelagos\Bundle\AppBundle\Controller\UI
 */
abstract class ReportController extends UIController
{
    //Datetime format used to put the date and time in the report file name
    const FILENAME_DATETIMEFORMAT = 'Y-m-d';
    //Timestamp format used to put the date and time in the report
    const INREPORT_TIMESTAMPFORMAT = 'm-d-Y H:i';

    /**
     * This method prevents non-DRPM to access the reports.
     *
     * @return Response
     */
    protected function checkAdminRestriction()
    {
        if (!$this->isGranted('ROLE_DATA_REPOSITORY_MANAGER')) {
            return $this->render('PelagosAppBundle:template:AdminOnly.html.twig');
        }
    }

    /**
     * This abstract function is where the data in the csv is generated.
     *
     * @param array|NULL $options Additional parameters needed to run the query.
     *
     * @return array  Return an indexed array.
     */
    abstract protected function queryData(array $options = null);

    /**
     * Write headers, labels, data to a csv response with a default or custom filename.
     *
     * @param array       $labels          Label row.
     * @param array       $data            Data rows.
     * @param string|NULL $customFileName  Non-generic csv output filename if needed.
     * @param array|NULL  $optionalHeaders Additional information to add in the reports.
     *
     * @return \StreamedResponse The csv response.
     */
    protected function writeCsvResponse(
        array $labels,
        array $data,
        $customFileName = null,
        array $optionalHeaders = null
    ) {
        //generic report name extracted from the controller's name
        $reportNameCamelCase = preg_replace('/Controller$/', '', (new \ReflectionClass($this))->getShortName());

        $response = new StreamedResponse(function () use ($labels, $data, $optionalHeaders, $reportNameCamelCase) {
            $handle = fopen('php://output', 'r+');
            //default headers
            fputcsv(
                $handle,
                array(strtoupper(preg_replace('/(?<!\ )[A-Z]/', ' $0', $reportNameCamelCase)))
            );
            fputcsv($handle, array(
                'Created at', (new DateTime('now'))->format(self::INREPORT_TIMESTAMPFORMAT)));
            fputcsv($handle, array());

            // write additional options to the csv.
            if ($optionalHeaders != null) {
                foreach ($optionalHeaders as $key => $value) {
                    fputcsv($handle, array($key, $value));
                }
                fputcsv($handle, array());
            }
            //write header
            fputcsv($handle, $labels);
            //write data
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        });

        //set filename: default file name extracted from the controller's name or custom filename
        if ($customFileName == null) {
            $defaultFileName = $reportNameCamelCase . '-' . (new DateTime('now'))
                ->format(self::FILENAME_DATETIMEFORMAT) . '.csv';
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $defaultFileName . '"');
        } else {
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $customFileName . '"');
        }
        $response->headers->set('Content-Type', 'text/csv');
        return $response;
    }
}
