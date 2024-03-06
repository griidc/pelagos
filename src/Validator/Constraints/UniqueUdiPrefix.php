<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Make sure the UDI prefix is not used.
 *
 * @Annotation
 */
class UniqueUdiPrefix extends Constraint
{
    /**
     * The default message to return when this constraint fails.
     *
     * @var string
     */
    public $message = 'This UDI prefix is already used.';
}
