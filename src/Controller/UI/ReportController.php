<?php

namespace App\Controller\UI;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use DateTime;

/**
 * This abstract controller provide a template for reports.
 */
abstract class ReportController extends AbstractController
{
    //Datetime format used to put the date and time in the report file name
    const FILENAME_DATETIMEFORMAT = 'Y-m-d';
    //Timestamp format used to put the date and time in the report
    const INREPORT_TIMESTAMPFORMAT = 'm-d-Y H:i';

    // A convenience for putting a blank line in the report
    const BLANK_LINE = '';

    /**
     * Write headers, labels, data to a csv response with a default or custom filename.
     *
     * @param array       $data           Data rows.
     * @param string|NULL $customFileName Non-generic csv output filename if needed.
     *
     * @return StreamedResponse The csv response.
     */
    protected function writeCsvResponse(
        array $data,
        $customFileName = null
    ) {
        $response = new StreamedResponse(function () use ($data) {

              // Byte Order Marker to indicate UTF-8
            echo chr(0xEF) . chr(0xBB) . chr(0xBF);

            $handle = fopen('php://output', 'r+');

            //write data
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        });

        //set filename: default file name extracted from the controller's name or custom filename
        if (null === $customFileName) {
            $customFileName = preg_replace('/Controller$/', '', (new \ReflectionClass($this))->getShortName()) . '-' .
                (new DateTime('now'))->format(self::FILENAME_DATETIMEFORMAT) .
                '.csv';
        }
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $customFileName . '"');
        $response->headers->set('Content-Type', 'text/csv');
        return $response;
    }

    /**
     * This returns the Report name extracted from the controller name and a creation timestamp.
     *
     * @return array Report Name to be displayed and a creation time stamp for the csv
     */
    protected function getDefaultHeaders()
    {
        //generic report name extracted from the controller's name
        $reportNameCamelCase = preg_replace('/Controller$/', '', (new \ReflectionClass($this))->getShortName());
        return array(
            array(trim(strtoupper(preg_replace('/(?<!\ )[A-Z]/', ' $0', $reportNameCamelCase)))),
            array('CREATED AT', (new DateTime('now'))->format(self::INREPORT_TIMESTAMPFORMAT)),
            array(self::BLANK_LINE)
        );
    }
}
