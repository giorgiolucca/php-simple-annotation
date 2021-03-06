<?php

namespace SimpleAnnotation;

use ReflectionClass;
use ReflectionException;
use SimpleAnnotation\Concerns\CacheInterface;
use SimpleAnnotation\Concerns\ParsedAnnotationInterface;
use SimpleAnnotation\Concerns\ParserInterface;

/**
 * Trait AnnotationTrait
 *
 * @package SimpleAnnotation
 */
trait AnnotationTrait
{
    /** @var ReflectionClass */
    private ReflectionClass $reflectionClass;

    /** @var ParserInterface */
    private ParserInterface $annotationParser;

    /** @var ParsedAnnotationInterface[] */
    private array $methodsAnnotations = [];

    /** @var ParsedAnnotationInterface[] */
    private array $propertiesAnnotations = [];

    /** @var CacheInterface */
    private $cache;

    /**
     * Annotation parser setter.
     *
     * @param ParserInterface $annotationParser
     * @return $this
     */
    public function setAnnotationParser(ParserInterface $annotationParser)
    {
        $this->annotationParser = $annotationParser;

        return $this;
    }

    /**
     * Cache handler setter.
     *
     * @param ?CacheInterface $cache
     * @return $this
     */
    public function setCacheHandler(?CacheInterface $cache = null)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Returns an array with the annotations of all properties of the class.
     *
     * @return ParsedAnnotationInterface[]
     */
    public final function getPropertiesAnnotations() : array
    {
        if ($this->cache !== null && $this->cache->has('properties')) {
            return $this->cache->get('properties');
        }

        foreach ($this->reflectionClass->getProperties() as $property) {
            $this->propertiesAnnotations[$property->name] = $this->annotationParser->parse((string)$property->getDocComment());
        }

        $this->cache !== null && $this->cache->set('properties', $this->propertiesAnnotations);

        return $this->propertiesAnnotations;
    }

    /**
     * Returns the annotations of the property given by the $key parameter.
     *
     * @param string $key
     * @return ParsedAnnotationInterface
     * @throws ReflectionException
     */
    public final function getPropertyAnnotations(string $key)
    {
        if ($this->cache !== null && $this->cache->has('properties')) {
            return ((array)$this->cache->get('properties'))[$key];
        }

        if ($this->cache !== null) {
            $this->getPropertiesAnnotations();
            $this->cache->set('properties', $this->propertiesAnnotations);
        } else {
            $this->propertiesAnnotations[$key] = $this->annotationParser->parse((string)$this->reflectionClass->getProperty($key)->getDocComment());
        }

        return $this->propertiesAnnotations[$key];
    }

    /**
     * Returns an array with the annotations of all methods of the class.
     *
     * @return ParsedAnnotationInterface[]
     */
    public final function getMethodsAnnotations() : array
    {
        if ($this->cache !== null && $this->cache->has('methods')) {
            return $this->cache->get('methods');
        }

        foreach ($this->reflectionClass->getMethods() as $method) {
            $this->methodsAnnotations[$method->name] = $this->annotationParser->parse((string)$method->getDocComment());
        }

        $this->cache !== null && $this->cache->set('methods', $this->methodsAnnotations);

        return $this->methodsAnnotations;
    }

    /**
     * Returns the annotations of the method given by the $key parameter.
     *
     * @param string $key
     * @return ParsedAnnotationInterface
     * @throws ReflectionException
     */
    public final function getMethodAnnotations(string $key)
    {
        if ($this->cache !== null && $this->cache->has('methods')) {
            return ((array)$this->cache->get('methods'))[$key];
        }

        if ($this->cache !== null) {
            $this->getMethodsAnnotations();
            $this->cache->set('methods', $this->methodsAnnotations);
        } else {
            $this->methodsAnnotations[$key] = $this->annotationParser->parse((string)$this->reflectionClass->getMethod($key)->getDocComment());
        }

        return $this->methodsAnnotations[$key];
    }

    /**
     * Return the annotations of the given class.
     *
     * @return ParsedAnnotationInterface
     */
    public final function getClassAnnotations()
    {
        if ($this->cache !== null && $this->cache->has('class')) {
            return $this->cache->get('class');
        }

        $classAnnotation = $this->annotationParser->parse((string)$this->reflectionClass->getDocComment());

        if ($this->cache !== null) {
            $this->cache->set('class', $classAnnotation);
        }

        return $classAnnotation;
    }
}
