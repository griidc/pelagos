<?php

namespace App\Validator\Constraints;

use App\Repository\FundingCycleRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Make sure the UDI prefix is not used.
 */
class UniqueUdiPrefixValidator extends ConstraintValidator
{
    public function __construct(private FundingCycleRepository $fundingCycleRepository) {}

    /**
     * Check to see if this UDI prefix is already used.
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($this->fundingCycleRepository->doesUdiPrefixExist($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
