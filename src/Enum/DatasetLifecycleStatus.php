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

    public function color(): string
    {
        return match ($this) {
            DatasetLifecycleStatus::AVAILABLE => 'green',
            DatasetLifecycleStatus::RESTRICTED => 'red',
            DatasetLifecycleStatus::SUBMITTED => 'blue',
            DatasetLifecycleStatus::IDENTIFIED => 'yellow',
            DatasetLifecycleStatus::NONE => 'black',
        };
    }
}
