<?php

namespace App\DataTransferObject;

use App\Entity\Dataset;

/**
 * Class DatasetMonitoringCsvRow.
 *
 * DTO structure to represents a row of dataset monitoring data for CSV export.
 */
final class DatasetMonitoringCsvRow
{
    public function __construct(
        public string $udi = '',
        public string $doi = '',
        public string $title = '',
        public string $status = '',
        public string $researchGroup = '',
        public string $fundingCycle = '',
        public string $fundingOrganization = '',
    ) {
    }

    /**
     * Creates a DatasetMonitoringCsvRow instance from a Dataset entity.
     * This is for convienience to easily convert a Dataset entity into a CSV row representation.
     *
     * @param Dataset $dataset
     * @return self
     */
    public static function fromDataset(Dataset $dataset): self
    {
        $rg = $dataset->getResearchGroup();
        $fc = $rg->getFundingCycle();
        $fo = $fc->getFundingOrganization();

        return new self(
            udi: $dataset->getUdi() ?? '',
            doi: $dataset->getDoi() ?? '',
            title: $dataset->getTitle() ?? '',
            // Status source changed per PELAGOS-5634 request.
            status: $dataset->getDatasetLifecycleStatus()->value,
            researchGroup: $rg->getName() ?? '',
            fundingCycle: $fc->getName() ?? '',
            fundingOrganization: $fo->getName() ?? '',
        );
    }

    /**
     * Creates an empty DatasetMonitoringCsvRow instance with only research group,
     * funding cycle, and funding organization. This is again for convienience to
     * more easily create a valid CSV row representation with only the specified fields.
     *
     * @param string $researchGroup
     * @param string $fundingCycle
     * @param string $fundingOrganization
     * @return self
     */
    public static function emptyDatasetRow(
        string $researchGroup,
        string $fundingCycle,
        string $fundingOrganization
    ): self {
        return new self(
            researchGroup: $researchGroup,
            fundingCycle: $fundingCycle,
            fundingOrganization: $fundingOrganization,
        );
    }
}
