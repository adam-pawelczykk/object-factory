<?php
/** @author Adam Pawełczyk */

namespace Tests\Utilities\Factories;

use Faker\Generator;
use ATPawelczyk\ObjectFactory\Interfaces\FactoryDefinitionInterface;
use Tests\Utilities\User;

/**
 * Class UserFactory
 * Sample of user factory
 */
class UserFactory implements FactoryDefinitionInterface
{
    /**
     * @inheritDoc
     */
    public function defaultProperties(Generator $faker, object $object): array
    {
        return [
            'name' => $faker->firstName(),
            'surname' => $faker->lastName()
        ];
    }

    /**
     * @inheritDoc
     */
    public static function definitionClass(): string
    {
        return User::class;
    }
}
