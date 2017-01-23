<?php

namespace Tests\helpers;

use Symfony\Component\Validator\ConstraintViolation;

/**
 * A trait containing custom assertions related to validation.
 */
trait ValidationAssertions
{
    /**
     * Assert that a list of violations conatins a given constraint for a given property.
     *
     * An optional expected constrain violation message may be passed as the fourth argument.
     *
     * @param mixed       $violations      An list of violations.
     * @param string      $property        The property to look for a constraint violation for.
     * @param string      $constraintClass The class of the expected constraint.
     * @param string|null $expectedMessage An optional expected contraint violation message.
     *
     * @return void
     */
    private function assertContainsConstraintForProperty(
        $violations,
        $property,
        $constraintClass,
        $expectedMessage = null
    ) {
        $foundConstraintForProperty = false;
        $message = null;
        foreach ($violations as $violation) {
            if ($violation instanceof ConstraintViolation
                and $violation->getPropertyPath() === $property
                and $violation->getConstraint() instanceof $constraintClass) {
                $foundConstraintForProperty = true;
                $message = $violation->getMessage();
            }
        }
        $this->assertTrue(
            $foundConstraintForProperty,
            "Constraint '$constraintClass' for property '$property' not found in violation list"
        );
        if ($foundConstraintForProperty and null !== $expectedMessage) {
            $this->assertEquals(
                $expectedMessage,
                $message,
                "Expected constraint violation message not found for $constraintClass constraint on $property property"
            );
        }
    }
}
