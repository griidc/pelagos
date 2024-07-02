<?php

namespace App\Enum;

/**
 * Types for Dataset Lifecycle Status.
 */
enum DatasetLifecycleStatus: string
{
    case AVAILABLE = 'Available';
    case RESTRICTED = 'Restricted';
    case SUBMITTED = 'Submitted';
    case IDENTIFIED = 'Identified';
    case NONE = 'None';

    /**
     * Returns the description of the Dataset Lifecycle Status.
     */
    public function description(): string
    {
        return match ($this) {
            DatasetLifecycleStatus::AVAILABLE => 'This dataset is available for download. ',
            DatasetLifecycleStatus::RESTRICTED => 'This dataset is restricted for download.',
            DatasetLifecycleStatus::SUBMITTED => 'This dataset has been submitted and is in the GRIIDC data package review process.',
            DatasetLifecycleStatus::IDENTIFIED => 'This dataset has been identified via a dataset information form. The dataset has not been submitted.',
            DatasetLifecycleStatus::NONE => 'The dataset is in an unknown state.',
        };
    }

    /**
     * Returns the color for the Dataset Lifecycle Status.
     */
    public function color(): string
    {
        return match ($this) {
            DatasetLifecycleStatus::AVAILABLE => '#337133',
            DatasetLifecycleStatus::RESTRICTED => '#C7434E',
            DatasetLifecycleStatus::SUBMITTED => '#3A86FF',
            DatasetLifecycleStatus::IDENTIFIED => '#FFC720',
            DatasetLifecycleStatus::NONE => 'black',
        };
    }

    /**
     * Returns the sort order for the Dataset Lifecycle Status.
     */
    public function sortOrder(): int
    {
        return match ($this) {
            DatasetLifecycleStatus::AVAILABLE => 1,
            DatasetLifecycleStatus::RESTRICTED => 2,
            DatasetLifecycleStatus::SUBMITTED => 3,
            DatasetLifecycleStatus::IDENTIFIED => 4,
            DatasetLifecycleStatus::NONE => 0,
        };
    }
}
