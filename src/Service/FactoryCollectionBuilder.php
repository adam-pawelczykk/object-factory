<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\ObjectFactory\Service;

use ATPawelczyk\ObjectFactory\Interfaces\FactoryDefinitionInterface;
use HaydenPierce\ClassFinder\ClassFinder;
use LogicException;
use Throwable;

class FactoryCollectionBuilder
{
    private $factoriesNamespace;
    /** @var string[] */
    private $factories;
    /** @var FactoryDefinitionInterface[] */
    private $instances;

    /**
     * FactoryCollectionBuilder constructor.
     * @param string $factoriesNamespace
     */
    public function __construct(string $factoriesNamespace)
    {
        $this->factoriesNamespace = $factoriesNamespace;
    }

    /**
     * @param string $className
     * @return FactoryDefinitionInterface
     * @throws Throwable
     */
    public function get(string $className): FactoryDefinitionInterface
    {
        if (null === $this->factories) {
            $this->build();
        }

        if (!isset($this->factories[$className])) {
            throw new LogicException("Factory for namespace $className not defined.");
        }
        if (!isset($this->instances[$className])) {
            // Initialize factory class namespace => factory for class namespace
            $this->instances[$className] = new $this->factories[$className];
        }

        return $this->instances[$className];
    }

    /**
     * @throws Throwable
     */
    private function build(): void
    {
        /** @var string[] $classes */
        $classes = ClassFinder::getClassesInNamespace($this->factoriesNamespace, ClassFinder::RECURSIVE_MODE);
        $this->factories = [];

        /** @var string $factoryClassName */
        foreach ($classes as $factoryClassName) {
            if (!in_array(FactoryDefinitionInterface::class, \Safe\class_implements($factoryClassName), true)) {
                continue;
            }
            if (!method_exists($factoryClassName, 'definitionClass')) {
                throw new LogicException("Static method definitionClass not defined in Factory {$factoryClassName}.");
            }

            $this->define($factoryClassName::definitionClass(), $factoryClassName);
        }
    }

    /**
     * @param string $definitionClassName
     * @param string $factoryClassName
     */
    private function define(string $definitionClassName, string $factoryClassName): void
    {
        $this->factories[$definitionClassName] = $factoryClassName;
    }
}
