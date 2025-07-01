<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\ObjectFactory\Service;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ReflectionPropertyAccessor implements PropertyAccessorInterface
{
    private $decoratedPropertyAccessor;

    /**
     * ReflectionPropertyAccessor constructor.
     * @param PropertyAccessorInterface $decoratedPropertyAccessor
     */
    public function __construct(PropertyAccessorInterface $decoratedPropertyAccessor)
    {
        $this->decoratedPropertyAccessor = $decoratedPropertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(&$objectOrArray, $propertyPath, $value): void
    {
        if (is_array($objectOrArray)) {
            $this->decoratedPropertyAccessor->setValue($objectOrArray, $propertyPath, $value);
        } else {
            $propertyReflectionProperty = $this->getPropertyReflectionProperty($objectOrArray, $propertyPath);

            if (null === $propertyReflectionProperty) {
                throw new NoSuchPropertyException;
            }

            if ($propertyReflectionProperty->getDeclaringClass()->getName() !== get_class($objectOrArray)) {
                $propertyReflectionProperty->setAccessible(true);

                $propertyReflectionProperty->setValue($objectOrArray, $value);

                return;
            }

            $setPropertyClosure = Closure::bind(
                function ($object) use ($propertyPath, $value) {
                    $object->{$propertyPath} = $value;
                },
                $objectOrArray,
                $objectOrArray
            );

            $setPropertyClosure($objectOrArray);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($objectOrArray, $propertyPath): mixed
    {
        try {
            return $this->decoratedPropertyAccessor->getValue($objectOrArray, $propertyPath);
        } catch (NoSuchPropertyException $exception) {
            $propertyReflectionProperty = $this->getPropertyReflectionProperty($objectOrArray, $propertyPath);
            if (null === $propertyReflectionProperty || !is_object($objectOrArray)) {
                throw $exception;
            }

            if ($propertyReflectionProperty->getDeclaringClass()->getName() !== get_class($objectOrArray)) {
                $propertyReflectionProperty->setAccessible(true);

                return $propertyReflectionProperty->getValue($objectOrArray);
            }

            $getPropertyClosure = Closure::bind(
                function ($object) use ($propertyPath) {
                    return $object->{$propertyPath};
                },
                $objectOrArray,
                $objectOrArray
            );

            return $getPropertyClosure($objectOrArray);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable($objectOrArray, $propertyPath): bool
    {
        return $this->decoratedPropertyAccessor->isWritable($objectOrArray, $propertyPath) || $this->propertyExists($objectOrArray, $propertyPath);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable($objectOrArray, $propertyPath): bool
    {
        return $this->decoratedPropertyAccessor->isReadable($objectOrArray, $propertyPath) || $this->propertyExists($objectOrArray, $propertyPath);
    }

    /**
     * @param object|array $objectOrArray
     * @param string $propertyPath
     *
     * @return bool Whether the property exists or not.
     * @throws ReflectionException
     */
    private function propertyExists($objectOrArray, $propertyPath): bool
    {
        return null !== $this->getPropertyReflectionProperty($objectOrArray, $propertyPath);
    }

    /**
     * @param object|array $objectOrArray
     * @param string $propertyPath
     * @return ReflectionProperty|null
     * @throws ReflectionException
     */
    private function getPropertyReflectionProperty($objectOrArray, $propertyPath): ?ReflectionProperty
    {
        if (false === is_object($objectOrArray)) {
            return null;
        }

        $reflectionClass = (new ReflectionClass(get_class($objectOrArray)));
        while ($reflectionClass instanceof ReflectionClass) {
            if ($reflectionClass->hasProperty($propertyPath)
                && false === $reflectionClass->getProperty($propertyPath)->isStatic()
            ) {
                return $reflectionClass->getProperty($propertyPath);
            }

            $reflectionClass = $reflectionClass->getParentClass();
        }

        return null;
    }

    private function __clone()
    {
    }
}
