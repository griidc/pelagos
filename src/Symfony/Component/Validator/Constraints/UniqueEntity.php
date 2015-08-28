<?php

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * A constraint that tests that a combination of fields are unique.
 *
 * @Annotation
 */
class UniqueEntity extends Constraint
{
    /**
     * The message to return when this constraint fails.
     *
     * @var string $message
     */
    public $message = 'This value must be unique';

    /**
     * The repository method to use to search.
     *
     * @var string $repositoryMethod
     */
    public $repositoryMethod = 'findBy';

    /**
     * A list of fields that, combined, must be unique.
     *
     * @var array $fields
     */
    public $fields = array();

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
        return array('fields');
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

    /**
     * Get the default option.
     *
     * @return string The default option.
     */
    public function getDefaultOption()
    {
        return 'fields';
    }
}
