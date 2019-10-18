<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * The validator for a constraint that tests that a string does not contain angle brackets (< or >).
 */
class NoAngleBracketsValidator extends ConstraintValidator
{
    /**
     * Validate method for this validator.
     *
     * Creates and adds a violation if angle brackets (< or >) are found in $value.
     *
     * @param string     $value      The value to test.
     * @param Constraint $constraint The constraint this validator is for.
     *
     * @return void
     */
    public function validate(string $value, Constraint $constraint)
    {
        if (preg_match('/[<>]/', $value, $matches)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
