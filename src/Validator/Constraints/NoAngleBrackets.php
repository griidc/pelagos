<?php

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * A constraint that tests that a string does not contain angle brackets (< or >).
 *
 * @Annotation
 */
class NoAngleBrackets extends Constraint
{
    /**
     * The default message to return when this constraint fails.
     *
     * @var string
     */
    public $message = 'This value cannot contain angle brackets (< or >)';
}
