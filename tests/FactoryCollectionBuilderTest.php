<?php
/** @author Adam Pawełczyk */

use ATPawelczyk\ObjectFactory\Interfaces\FactoryDefinitionInterface;
use ATPawelczyk\ObjectFactory\Service\FactoryCollectionBuilder;
use PHPUnit\Framework\TestCase;
use Tests\Utilities\Factories\UserFactory;

class FactoryCollectionBuilderTest extends TestCase
{
    /** @var FactoryCollectionBuilder */
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new FactoryCollectionBuilder('Tests\Utilities');
    }

    /**
     * @throws Throwable
     */
    public function testShouldReturnDefinedFactory()
    {
        $definition = $this->builder->get(UserFactory::definitionClass());

        $this->assertInstanceOf(
            FactoryDefinitionInterface::class,
            $definition
        );
    }
}
