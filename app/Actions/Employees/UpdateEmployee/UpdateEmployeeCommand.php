<?php

namespace App\Actions\Employees\UpdateEmployee;

final class UpdateEmployeeCommand
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $login;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string|null
     */
    private $eds;
    /**
     * @var string|null
     */
    private $timezone;
    /**
     * @var string|null
     */
    private $password;
    /**
     * @var int|null
     */
    private $pvId;
    /**
     * @var int[]
     */
    private $pvs;
    /**
     * @var int
     */
    private $blocked;
    /**
     * @var string|null
     */
    private $validityEdsStart;
    /**
     * @var string|null
     */
    private $validityEdsEnd;
    /**
     * @var int[]
     */
    private $roles;
    /**
     * @var int[]
     */
    private $permissions;

    /**
     * @param int $id
     * @param string $name
     * @param string $login
     * @param string $email
     * @param string|null $eds
     * @param string|null $timezone
     * @param string|null $password
     * @param int|null $pvId
     * @param int[] $pvs
     * @param int $blocked
     * @param string|null $validityEdsStart
     * @param string|null $validityEdsEnd
     * @param int[] $roles
     * @param int[] $permissions
     */
    public function __construct(
        int     $id,
        string  $name,
        string  $login,
        string  $email,
        ?string $eds,
        ?string $timezone,
        ?string $password,
        ?int    $pvId,
        array   $pvs,
        int     $blocked,
        ?string $validityEdsStart,
        ?string $validityEdsEnd,
        array   $roles,
        array   $permissions
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->login = $login;
        $this->email = $email;
        $this->eds = $eds;
        $this->timezone = $timezone;
        $this->password = $password;
        $this->pvId = $pvId;
        $this->pvs = $pvs;
        $this->blocked = $blocked;
        $this->validityEdsStart = $validityEdsStart;
        $this->validityEdsEnd = $validityEdsEnd;
        $this->roles = $roles;
        $this->permissions = $permissions;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getEds(): ?string
    {
        return $this->eds;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPvId(): ?int
    {
        return $this->pvId;
    }

    public function getPvs(): array
    {
        return $this->pvs;
    }

    public function getBlocked(): int
    {
        return $this->blocked;
    }

    public function getValidityEdsStart(): ?string
    {
        return $this->validityEdsStart;
    }

    public function getValidityEdsEnd(): ?string
    {
        return $this->validityEdsEnd;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }
}