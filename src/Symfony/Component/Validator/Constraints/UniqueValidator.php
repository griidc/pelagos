<?php

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * The validator for a constraint that tests if a value is unique.
 */
class UniqueValidator extends ConstraintValidator
{
    /**
     * Validate method for this validator.
     *
     * Creates and adds a violation if an entity exists in persistence with $value for this property.
     *
     * @param string     $value      The value to check for uniqueness.
     * @param Constraint $constraint The constraint this validator is for.
     *
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        $comp = new \Pelagos\Component;
        $em = $comp->getEntityManager();
        $entities = $em->getRepository(
            $this->context->getClassName()
        )->findBy(
            array(
                $this->context->getPropertyName() => $value
            )
        );
        if (count($entities) > 0) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
