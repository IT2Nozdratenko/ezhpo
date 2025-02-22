<?php

namespace App\Actions\User\UpdateUser;

use App\User;

final class UpdateUserCommand
{
    /**
     * @var User
     */
    private $user;
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
    private $password;
    /**
     * @var string|null
     */
    private $timezone;
    /**
     * @var int[]
     */
    private $roles;
    /**
     * @var int[]
     */
    private $permissions;

    /**
     * @param User $user
     * @param string $login
     * @param string $email
     * @param string|null $password
     * @param string|null $timezone
     * @param int[] $roles
     * @param int[] $permissions
     */
    public function __construct(
        User    $user,
        string  $login,
        string  $email,
        ?string $password,
        ?string $timezone,
        array   $roles,
        array   $permissions
    )
    {
        $this->user = $user;
        $this->login = $login;
        $this->email = $email;
        $this->password = $password;
        $this->timezone = $timezone;
        $this->roles = $roles;
        $this->permissions = $permissions;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
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