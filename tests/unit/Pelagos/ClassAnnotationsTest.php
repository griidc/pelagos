<?php

namespace Pelagos;

use PHPUnit\Framework\TestCase;
/**
 * Unit tests for Pelagos\ClassAnnotations.
 *
 * @group Pelagos
 */
class ClassAnnotationsTest extends TestCase
{
    /**
     * Variable to hold an instance of ClassAnnotations.
     *
     * @var mixed
     */
    private $testAnnotationClass;
    
    /**
     * Static vraible to hold an array of class annotations.
     *
     * @var array
     */
    protected static $testClassAnnotation = array(
        'foo' => 'bar',
        'return' => 'something'
    );
 
    /**
     * Static variable to hold an array of method annotations.
     *
     * @var array
     */
    protected static $testMethodAnnotation = array(
        'type' => 'sometype',
        'return' => 'void'
    );
 
    /**
     * Static variable to hold an array of property annotations.
     *
     * @var array
     */
    protected static $testPropertyAnnotation = array(
        'var' => 'string',
        'something' => 'else',
        'blabla' => 'ding ding ding'
    );
    
    /**
     * Test set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->testAnnotationClass = new ClassAnnotations('\\Pelagos\\TestClass');
    }
    
    /**
     * Test getting class annotations.
     *
     * @return void
     */
    public function testGetClassAnnotations()
    {
        $this->assertEquals(
            $this->testAnnotationClass->getClassAnnotations(),
            self::$testClassAnnotation
        );
    }
    
    /**
     * Test getting method annotations.
     *
     * @return void
     */
    public function testGetMethodAnnotations()
    {
        $this->assertEquals(
            $this->testAnnotationClass->getMethodAnnotations('testFunction'),
            self::$testMethodAnnotation
        );
    }
    
    /**
     * Test getting property annotations.
     *
     * @return void
     */
    public function testGetPropertyAnnotations()
    {
        $this->assertEquals(
            $this->testAnnotationClass->getPropertyAnnotations('testProp'),
            self::$testPropertyAnnotation
        );
    }
}
