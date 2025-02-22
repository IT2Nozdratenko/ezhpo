<?php

namespace App\Actions\User\CreateUser;

use App\Enums\UserEntityType;

final class CreateUserCommand
{
    /**
     * @var UserEntityType
     */
    private $entityType;
    /**
     * @var string
     */
    private $login;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string|null
     */
    private $timezone;
    /**
     * @var string|null
     */
    private $apiToken;
    /**
     * @var int|null
     */
    private $companyId;
    /**
     * @var int|null
     */
    private $autoCreated;
    /**
     * @var int[]
     */
    private $roles;
    /**
     * @var int[]
     */
    private $permissions;

    /**
     * @param UserEntityType $entityType
     * @param string $login
     * @param string $email
     * @param string $password
     * @param string|null $timezone
     * @param string|null $apiToken
     * @param int|null $companyId
     * @param int|null $autoCreated
     * @param int[] $roles
     * @param int[] $permissions
     */
    public function __construct(
        UserEntityType $entityType,
        string         $login,
        string         $email,
        string         $password,
        ?string        $timezone,
        ?string        $apiToken,
        ?int           $companyId,
        ?int           $autoCreated,
        array          $roles,
        array          $permissions
    )
    {
        $this->entityType = $entityType;
        $this->login = $login;
        $this->email = $email;
        $this->password = $password;
        $this->timezone = $timezone;
        $this->apiToken = $apiToken;
        $this->companyId = $companyId;
        $this->autoCreated = $autoCreated;
        $this->roles = $roles;
        $this->permissions = $permissions;
    }

    public function getEntityType(): UserEntityType
    {
        return $this->entityType;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function getCompanyId(): ?int
    {
        return $this->companyId;
    }

    public function getAutoCreated(): ?int
    {
        return $this->autoCreated;
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