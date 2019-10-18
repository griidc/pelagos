<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * The validator for a constraint that tests that two fields meet a comparison test.
 */
class ComparePropertiesValidator extends ConstraintValidator
{
    /**
     * Validate method for this validator.
     *
     * @param mixed      $entity     The entity to validate.
     * @param Constraint $constraint The constraint this validator is for.
     *
     * @throws UnexpectedTypeException       When $constraint is not a CompareProperties.
     * @throws UnexpectedTypeException       When $constraint->errorPath is not a string or null.
     * @throws ConstraintDefinitionException When $constraint->left is not a valid property of the class.
     * @throws ConstraintDefinitionException When $constraint->right is not a valid property of the class.
     * @throws ConstraintDefinitionException When $constraint->comparison is not a valid comparison operator.
     *
     * @return void
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof CompareProperties) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\CompareProperties');
        }

        if (null !== $constraint->errorPath && !is_string($constraint->errorPath)) {
            throw new UnexpectedTypeException($constraint->errorPath, 'string or null');
        }

        $reflection = new \ReflectionClass($entity);

        if (!$reflection->hasProperty($constraint->left)) {
            throw new ConstraintDefinitionException(
                sprintf('"%s" is not a valid property of %s.', $constraint->left, get_class($entity))
            );
        }
        $leftValueProperty = $reflection->getProperty($constraint->left);
        $leftValueProperty->setAccessible(true);
        $leftValue = $leftValueProperty->getValue($entity);

        if (!$reflection->hasProperty($constraint->right)) {
            throw new ConstraintDefinitionException(
                sprintf('"%s" is not a valid property of %s.', $constraint->right, get_class($entity))
            );
        }
        $rightValueProperty = $reflection->getProperty($constraint->right);
        $rightValueProperty->setAccessible(true);
        $rightValue = $rightValueProperty->getValue($entity);

        if ($constraint->ignoreNull and ($leftValue === null or $rightValue === null)) {
            return;
        }

        switch ($constraint->comparison) {
            case 'LessThan':
                if ($leftValue < $rightValue) {
                    return;
                }
                break;
            case 'LessThanEqualTo':
                if ($leftValue <= $rightValue) {
                    return;
                }
                break;
            case 'GreaterThan':
                if ($leftValue > $rightValue) {
                    return;
                }
                break;
            case 'GreaterThanEqualTo':
                if ($leftValue >= $rightValue) {
                    return;
                }
                break;
            case 'EqualTo':
                if ($leftValue == $rightValue) {
                    return;
                }
                break;
            case 'NotEqualTo':
                if ($leftValue != $rightValue) {
                    return;
                }
                break;
            default:
                throw new ConstraintDefinitionException(
                    sprintf('%s is not a valid comparison.', $constraint->comparison)
                );
        }

        $errorPath = $constraint->errorPath !== null ? $constraint->errorPath : $constraint->right;

        if ($this->context instanceof ExecutionContextInterface) {
            $this->context->buildViolation($constraint->message)
                ->atPath($errorPath)
                ->addViolation();
        } else {
            $this->buildViolation($constraint->message)
                ->atPath($errorPath)
                ->addViolation();
        }
    }
}
