<?php

namespace App\Tests\ExpressionLanguage;

use App\Entity\ResearchGroup;
use App\ExpressionLanguage\FunctionProvider;
use JMS\Serializer\SerializationContext;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Unit tests for App\Util\Base32Generator.php
 */
class FunctionProviderTest extends TestCase
{
    /**
     * Test Expressions Language Functions.
     */
    public function testElFunctions()
    {
        $expressionLanguage = new ExpressionLanguage();
        $expressionLanguage->registerProvider(new FunctionProvider());

        $object = $this->createMock(ResearchGroup::class);
        $context = SerializationContext::create();

        $groups = array('test');
        $context->setGroups($groups);

        var_dump($expressionLanguage->compile("hasNoGroups($object, $context)"));

    }

}
