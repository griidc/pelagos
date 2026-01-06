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
    case INCOMPLETE = 'Incomplete';
    case PENDING = 'Pending';
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
            DatasetLifecycleStatus::INCOMPLETE => 'A DIF is created, but has not been completed.',
            DatasetLifecycleStatus::PENDING => 'This DIF is pending approval.',
            DatasetLifecycleStatus::NONE => 'The dataset is in an unknown state.',
        };
    }

    /**
     * Returns the color for the Dataset Lifecycle Status.
     */
    public function color(): string
    {
        return match ($this) {
            DatasetLifecycleStatus::AVAILABLE => '#28a745',
            DatasetLifecycleStatus::RESTRICTED => '#dc3545',
            DatasetLifecycleStatus::SUBMITTED => '#007bff',
            DatasetLifecycleStatus::IDENTIFIED => '#ffc107',
            DatasetLifecycleStatus::INCOMPLETE => '#6c757d',
            DatasetLifecycleStatus::PENDING => '#ced4da',
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
