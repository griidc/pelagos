<?php

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * A constraint that tests that two fields meet a comparison test.
 *
 * @Annotation
 */
class CompareProperties extends Constraint
{
    /**
     * The message to return when this constraint fails.
     *
     * @var string $message
     */
    public $message = 'Fields failed comparison test';

    /**
     * The name of the property to use on the left side of the compare.
     *
     * @var string $left
     */
    public $left = null;

    /**
     * The name of the property to use on the right side of the compare.
     *
     * @var string $right
     */
    public $right = null;

    /**
     * The comparison operator to use.
     *
     * @var string $compare
     *
     * @Enum({"LessThan","LessThanEqualTo","GreaterThan","GreaterThanEqualTo","EqualTo","NotEqualTo"})
     */
    public $comparison = null;

    /**
     * Which field the error should be bound to.
     *
     * @var string $errorPath
     */
    public $errorPath = null;

    /**
     * Whether or not to ignore null values for fields.
     *
     * @var boolean $ignoreNull
     */
    public $ignoreNull = true;

    /**
     * Get the required options.
     *
     * @return array The list of required options.
     */
    public function getRequiredOptions()
    {
        return array('left','right','comparison');
    }

    /**
     * Get the targets this validator can be applied to.
     *
     * @return const The CLASS_CONSTRAINT constant.
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
