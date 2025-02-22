<?php

namespace App\Actions\Employees\RestoreEmployee;

final class RestoreEmployeeCommand
{
    /**
     * @var int
     */
    private $id;

    /**
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}