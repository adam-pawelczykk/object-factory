<?php
/** @author Adam Pawełczyk */

use PHPUnit\Framework\TestCase;

use ATPawelczyk\ObjectFactory\ObjectFactory;
use Tests\Utilities\User;

class ObjectFactoryTest extends TestCase
{
    private $factory;

    protected function setUp(): void
    {
        $this->factory = new ObjectFactory('Tests\Utilities');
    }

    /**
     * @throws Throwable
     */
    public function testShouldCreateUserClassFromFactory()
    {
        $user = $this->factory->create(User::class);

        $this->assertNotNull($user->getName());
        $this->assertNotNull($user->getSurname());
    }
}
