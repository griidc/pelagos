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
    public function testElFunctionsWithGroups()
    {
        $expressionLanguage = new ExpressionLanguage();
        $expressionLanguage->registerProvider(new FunctionProvider());

        $object = $this->createMock(ResearchGroup::class);
        $context = SerializationContext::create();

        $groups = array('test');
        $context->setGroups($groups);

        $variables = array(
            'object' => $object,
            'context' => $context,
        );

        $names = array('object', 'context');
        $result = $expressionLanguage->compile('hasNoGroups(object, context)', $names);
        $this->assertSame("true", $result);


        $result = $expressionLanguage->evaluate('hasNoGroups(object, context)', $variables);
        $this->assertSame(false, $result);
    }

    /**
     * Test Expressions Language Functions.
     */
    public function testElFunctionsNoGroups()
    {
        $expressionLanguage = new ExpressionLanguage();
        $expressionLanguage->registerProvider(new FunctionProvider());

        $object = $this->createMock(ResearchGroup::class);
        $context = SerializationContext::create();

        $variables = array(
            'object' => $object,
            'context' => $context,
        );

        $names = array('object', 'context');
        $result = $expressionLanguage->compile('hasNoGroups(object, context)', $names);
        $this->assertSame("true", $result);


        $result = $expressionLanguage->evaluate('hasNoGroups(object, context)', $variables);
        $this->assertSame(true, $result);

    }

}
