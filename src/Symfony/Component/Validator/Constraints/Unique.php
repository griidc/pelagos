<?php

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * A constraint that tests whether a value is unique.
 *
 * @Annotation
 */
class Unique extends Constraint
{
    /** 
     * The default message to return when this constraint fails.
     *
     * @var string $message
     */
    public $message = 'This value must be unique';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::PROPERTY_CONSTRAINT;
    }
}
