<?php

namespace Pelagos;

/**
 * A class that has functions that read class annotations
 */
class ClassAnnotations
{
    /**
     * Name of a funding cycle.
     *
     * @var \ReflectionClass $classReflection
     * @access private
     */
    private $classReflection;
    
    /**
     * Contructor for this class
     *
     * @param string $class Class name.
     *
     * @access public
     *
     * @return void
     */
    public function __construct($class)
    {
        $this->classReflection = new \ReflectionClass($class);
    }
    
    /**
     * gets class annotations
     *
     * @access public
     *
     * @return array Array of annotations
     */
    public function getClassAnnotations()
    {
        return $this->convertAnnotations($this->classReflection->getDocComment());
    }
    
    /**
     * Get annotations for a method.
     *
     * @param string $methodName Method name.
     *
     * @access public
     *
     * @return array Array of annotations
     */
    public function getMethodAnnotations($methodName)
    {
        return $this->convertAnnotations($this->classReflection->getMethod($methodName)->getDocComment());
    }
    
    /**
     * Get annotations for a property.
     *
     * @param string $propertyName Class name.
     *
     * @access public
     *
     * @return array Array of annotations
     */
    public function getPropertyAnnotations($propertyName)
    {
        return $this->convertAnnotations($this->classReflection->getProperty($propertyName)->getDocComment());
    }
    
    /**
     * Converts docblock annotations to key value array
     *
     * @param string $docBlock a document block string containing annotations.
     *
     * @access private
     *
     * @return array An array of parameters.
     */
    private function convertAnnotations($docBlock)
    {
        $parameters = array();
        preg_match_all('#@(.*?)\n#s', $docBlock, $annotations);
        foreach ($annotations[1] as $annotation) {
            $valuePair = preg_split("/[\s]+/", $annotation, 2);
            $parameters[$valuePair[0]] = trim($valuePair[1]);
        }
        
        return $parameters;
    }
}
