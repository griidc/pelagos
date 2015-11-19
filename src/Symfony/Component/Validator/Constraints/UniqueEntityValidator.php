<?php

namespace Symfony\Component\Validator\Constraints;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Pelagos\Factory\EntityManagerFactory;

/**
 * The validator for a constraint that tests that a combination of fields are unique.
 */
class UniqueEntityValidator extends ConstraintValidator
{
    /**
     * Validate method for this validator.
     *
     * @param object     $entity     The entity to validate.
     * @param Constraint $constraint The constraint this validator is for.
     *
     * @throws UnexpectedTypeException       When $constraint is not a UniqueEntity.
     * @throws UnexpectedTypeException       When $constraint->fields is not an array or string.
     * @throws UnexpectedTypeException       When $constraint->errorPath is not a string or null.
     * @throws ConstraintDefinitionException When no field is specified.
     * @throws ConstraintDefinitionException When a specified field is not mapped in Doctrine.
     * @throws ConstraintDefinitionException When more than one identifier field in an associated entity is used.
     *
     * @return void
     */
    public function validate($entity, Constraint $constraint)
    {
        $em = EntityManagerFactory::create();

        if (!$constraint instanceof UniqueEntity) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\UniqueEntity');
        }

        if (!is_array($constraint->fields) && !is_string($constraint->fields)) {
            throw new UnexpectedTypeException($constraint->fields, 'array');
        }

        if (null !== $constraint->errorPath && !is_string($constraint->errorPath)) {
            throw new UnexpectedTypeException($constraint->errorPath, 'string or null');
        }

        $fields = (array) $constraint->fields;
        if (0 === count($fields)) {
            throw new ConstraintDefinitionException('At least one field has to be specified.');
        }

        $class = $em->getClassMetadata(get_class($entity));

        $criteria = array();
        foreach ($fields as $fieldName) {
            if (!$class->hasField($fieldName) && !$class->hasAssociation($fieldName)) {
                throw new ConstraintDefinitionException(
                    sprintf(
                        'The field "%s" is not mapped by Doctrine, so it cannot be validated for uniqueness.',
                        $fieldName
                    )
                );
            }

            $criteria[$fieldName] = $class->reflFields[$fieldName]->getValue($entity);

            if ($constraint->ignoreNull && null === $criteria[$fieldName]) {
                return;
            }

            if (null !== $criteria[$fieldName] && $class->hasAssociation($fieldName)) {
                // Ensure the Proxy is initialized before using reflection to
                // read its identifiers. This is necessary because the wrapped
                // getter methods in the Proxy are being bypassed.
                $em->initializeObject($criteria[$fieldName]);
                $relatedClass = $em->getClassMetadata($class->getAssociationTargetClass($fieldName));
                $relatedId = $relatedClass->getIdentifierValues($criteria[$fieldName]);
                if (count($relatedId) > 1) {
                    throw new ConstraintDefinitionException(
                        'Associated entities are not allowed to have more than one identifier field to be ' .
                        'part of a unique constraint in: ' . $class->getName() . '#' . $fieldName
                    );
                }
                $criteria[$fieldName] = array_pop($relatedId);
            }
        }

        $repository = $em->getRepository(get_class($entity));
        $result = $repository->{$constraint->repositoryMethod}($criteria);

        if (count($result) === 0) {
            // If no entity matched the query criteria, the criteria is unique.
            return;
        }
        if (count($result) === 1) {
            // If one entity matched the query criteria, get it.
            $foundEntity = $result instanceof \Iterator ? $result->current() : current($result);
            if ($foundEntity->getId() === $entity->getId()) {
                // If it's id is the same as the id of entity being validated, the criteria is unique.
                return;
            }
        }

        $errorPath = null !== $constraint->errorPath ? $constraint->errorPath : $fields[0];
        $invalidValue = isset($criteria[$errorPath]) ? $criteria[$errorPath] : $criteria[$fields[0]];

        if ($this->context instanceof ExecutionContextInterface) {
            $this->context->buildViolation($constraint->message)
                ->atPath($errorPath)
                ->setInvalidValue($invalidValue)
                ->addViolation();
        } else {
            $this->buildViolation($constraint->message)
                ->atPath($errorPath)
                ->setInvalidValue($invalidValue)
                ->addViolation();
        }
    }
}
