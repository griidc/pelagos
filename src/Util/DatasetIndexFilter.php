<?php

namespace App\Util;

use App\Entity\Dataset;
use App\Entity\DIF;
use App\Util\FundingOrgFilter;

/**
 * A utility to determine if a Dataset should be indexed.
 */
class DatasetIndexFilter
{
    /**
     * Returns true if passed dataset should be indexed.
     *
     * @param \App\Entity\Dataset $dataset The dataset to considered for indexing.
     *
     * @return boolean If the dataset should be indexed, which is all but datasets of only an unapproved DIF.
     */
    public static function indexable(Dataset $dataset): bool
    {
        return (FundingOrgFilter::canIndex($dataset) and ($dataset->hasDatasetSubmission() === true or ($dataset->hasDif() === true and $dataset->getDif()->getStatus() === DIF::STATUS_APPROVED)));
    }
}
