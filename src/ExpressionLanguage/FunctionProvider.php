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
                return "true";
            }, function ($arguments, $object, $context) {
                /** @var \JMS\Serializer\SerializationContext $context */
                // dump(get_class($object));
                if(empty($context)) {
                    $context = $arguments['context'];
                }
                $groups = $context->hasAttribute('groups') ? $context->getAttribute('groups') : null;
                // dump($groups);
                // dd(!is_array($groups));
                return !$context->hasAttribute('groups');
            }),
        ];
    }
}
