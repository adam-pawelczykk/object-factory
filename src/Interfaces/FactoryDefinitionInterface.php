<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\ObjectFactory\Interfaces;

use Faker\Generator;

interface FactoryDefinitionInterface
{
    /**
     * @param Generator $faker
     * @param object $object
     * @return array
     */
    public function defaultProperties(Generator $faker, object $object): array;

    /**
     * @return class-string<FactoryDefinitionInterface>
     */
    public static function definitionClass(): string;
}
