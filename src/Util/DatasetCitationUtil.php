<?php

namespace App\Util;

use App\Entity\Dataset;

/**
 * A utility to create a Dataset Citation Text.
 */
class DatasetCitationUtil
{
    /**
     * Creates the and return the Dataset Citation Text.
     *
     * @param Dataset $dataset The Dataset that has the data.
     *
     * @return string The Dataset Citation string.
     */
    public static function getCitation(Dataset $dataset) :string
    {
        $title = $dataset->getTitle();
        $udi = $dataset->getUdi();
        $author = $dataset->getAuthors();
        $year = null;
        if ($dataset->getAcceptedDate() instanceof \Datetime) {
            $year = $dataset->getAcceptedDate()->format('Y');
        }
        $doi = $dataset->getDoi();

        $citationString = '';

        $citationString .= (!empty($author) ? "$author. " : '');
        $citationString .= (!empty($year) ? "($year). " : '');
        $citationString .= "$title. ";
        $citationString .= 'Distributed by: GRIIDC '
            . '(GRIIDC), Harte Research Institute, Texas A&M University-Corpus Christi. ';

        if ($doi instanceof DOI) {
            $citationString .= 'doi:' . $doi->getDoi();
        } else {
            $citationString .= "Available from: http://data.gulfresearchinitiative.org/data/$udi";
        }
        return $citationString;
    }
}
