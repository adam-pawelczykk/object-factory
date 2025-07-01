<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\ObjectFactory;

use ATPawelczyk\ObjectFactory\Interfaces\FactoryDefinitionInterface;
use ATPawelczyk\ObjectFactory\Service\FactoryCollectionBuilder;
use ATPawelczyk\ObjectFactory\Service\ReflectionPropertyAccessor;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorBuilder;
use Faker\Factory;
use Faker\Generator;
use ReflectionClass;
use ReflectionException;
use Throwable;

/**
 * Class ObjectFactory
 * @package ATPawelczyk\ObjectFactory
 */
class ObjectFactory
{
    /** @var FactoryCollectionBuilder */
    private $collection;
    /** @var Generator */
    private $faker;
    /** @var ReflectionPropertyAccessor|null */
    private $propertyAccessor;
    private $strictMode = true;

    /**
     * ObjectFactory constructor.
     * @param string $factoriesNamespace
     * @param string $fakerLocale
     */
    public function __construct(string $factoriesNamespace, string $fakerLocale = 'pl_PL')
    {
        $this->collection = new FactoryCollectionBuilder($factoriesNamespace);
        $this->faker = Factory::create($fakerLocale);
    }

    /**
     * @return Generator
     */
    public function getFaker(): Generator
    {
        return $this->faker;
    }

    /**
     * @param bool $strictMode
     * @return void
     */
    public function setStrictMode(bool $strictMode): void
    {
        $this->strictMode = $strictMode;
    }

    /**
     * @param string $className
     * @param mixed[] $propertyValues
     * @return object
     * @throws Throwable
     * @psalm-param class-string<T> $className
     * @psalm-return T
     * @template T of object
     */
    public function create(string $className, array $propertyValues = []): object
    {
        $factory = $this->getFactory($className);
        $object = $this->createInstance($className);
        $merged = array_merge($factory->defaultProperties($this->faker, $object), $propertyValues);

        foreach ($merged as $propertyPath => $value) {
            $this->setPropertyValue(
                $object,
                $propertyPath,
                is_callable($value) ? $value($this, $object) : $value
            );
        }

        return $object;
    }

    /**
     * @param string $className
     * @param int $qty
     * @param mixed[] $propertyValues
     * @return mixed[]
     * @throws ReflectionException
     * @throws Throwable
     */
    public function createMany(string $className, int $qty, array $propertyValues = []): array
    {
        $objects = [];

        for ($i = 0; $i < $qty; $i++) {
            $objects[] = $this->create($className, $propertyValues);
        }

        return $objects;
    }

    /**
     * @param string $className
     * @return FactoryDefinitionInterface
     * @throws Throwable
     */
    private function getFactory(string $className): FactoryDefinitionInterface
    {
        return $this->collection->get($className);
    }

    /**
     * @return ReflectionPropertyAccessor
     */
    public function getPropertyAccessor(): ReflectionPropertyAccessor
    {
        if (null === $this->propertyAccessor) {
            $propertyAccessorBuilder = new PropertyAccessorBuilder();
            $propertyAccessorBuilder->disableExceptionOnInvalidIndex();

            $this->propertyAccessor = new ReflectionPropertyAccessor(
                $propertyAccessorBuilder->getPropertyAccessor()
            );
        }

        return $this->propertyAccessor;
    }

    /**
     * @param string $className
     * @return object
     * @throws ReflectionException
     */
    protected function createInstance(string $className): object
    {
        return (new ReflectionClass($className))->newInstanceWithoutConstructor();
    }

    /**
     * @param object $object
     * @param string $propertyPath
     * @param mixed $value
     */
    protected function setPropertyValue(object $object, string $propertyPath, $value): void
    {
        try {
            $this->getPropertyAccessor()->setValue($object, $propertyPath, $value);
        } catch (NoSuchPropertyException $exception) {
            if ($this->strictMode) {
                $objectName = get_class($object);

                throw new NoSuchPropertyException(
                    "Property {$propertyPath} not exist in {$objectName}.",
                    0,
                    $exception
                );
            }

            $object->{$propertyPath} = $value;
        }
    }
}
