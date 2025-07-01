<?php
/** @author Adam Pawełczyk */

namespace Tests\Utilities;

class User
{
    private $name;
    private $surname;

    /**
     * User constructor.
     * @param string $name
     * @param string $surname
     */
    public function __construct(string $name, string $surname)
    {

        $this->name = $name;
        $this->surname = $surname;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSurname(): string
    {
        return $this->surname;
    }
}
