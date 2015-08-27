<?php

namespace Pelagos;

/**
 * Unit tests for Pelagos\ClassAnnotations.
 *
 * @group Pelagos
 */
class ClassAnnotationsTest extends \PHPUnit_Framework_TestCase
{
    private $testAnnotationClass;
    
    protected static $testClassAnnotation = array(
        'foo' => "bar",
        'return' => "something"
    );
 
    protected static $testMethodAnnotation = array(
        'type' => "sometype",
        'return' => "baz"
    );
 
    protected static $testPropertyAnnotation = array(
        'something' => "else",
        'blabla' => "ding ding ding"
    );
    
    protected function setUp()
    {
        $this->testAnnotationClass = new ClassAnnotations('\\Pelagos\\TestClass');
    } 
    
    public function testGetClassAnnotations()
    {
        $this->assertEquals(
            $this->testAnnotationClass->getClassAnnotations(),
            self::$testClassAnnotation
        );
    }
    
    public function testGetMethodAnnotations()
    {
        $this->assertEquals(
            $this->testAnnotationClass->getMethodAnnotations('testFunction'),
            self::$testMethodAnnotation
        );
    }
    
    public function testGetPropertyAnnotations()
    {
        $this->assertEquals(
            $this->testAnnotationClass->getPropertyAnnotations('testProp'),
            self::$testPropertyAnnotation
        );
    }
}