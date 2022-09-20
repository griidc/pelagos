<?php

namespace App\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * Function Provider for JMS serializer expression language.
 */
class FunctionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * Return the functions.
     *
     * @return void
     */
    public function getFunctions()
    {
        return [
            new ExpressionFunction('hasNoGroups', function ($object, $context) {
                return "!array($context->getAttribute('groups')";
            }, function ($arguments) {
                /** @var \JMS\Serializer\SerializationContext $context */
                $context = $arguments['context'];
                $groups = $context->getAttribute('groups');
                return !is_array($groups);
            }),
        ];
    }
}
