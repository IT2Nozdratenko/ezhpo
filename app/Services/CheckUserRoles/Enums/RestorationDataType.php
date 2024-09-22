<?php
declare(strict_types=1);

namespace App\Services\CheckUserRoles\Enums;

final class RestorationDataType
{
    const CREATED_USERS = 'created_users';
    const DETACHED_ROLES_FROM_USER = 'detached_roles_from_user';
    const DELETED_USERS = 'deleted_users';

    /** @var string */
    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    public static function fromString(string $value): self
    {
        switch ($value) {
            case self::CREATED_USERS:
                return self::createdUsers();
            case self::DETACHED_ROLES_FROM_USER:
                return self::detachedRolesFromUser();
            case self::DELETED_USERS:
                return self::deletedUsers();
            default:
                throw new \DomainException('Unknown restoration data type: ' . $value);
        }
    }

    public static function createdUsers(): self
    {
        return new self(self::CREATED_USERS);
    }

    public static function detachedRolesFromUser(): self
    {
        return new self(self::DETACHED_ROLES_FROM_USER);
    }

    public static function deletedUsers(): self
    {
        return new self(self::DELETED_USERS);
    }

    public function __toString(): string
    {
        return $this->value();
    }

    public static function cases(): array
    {
        return [
           self::CREATED_USERS,
           self::DETACHED_ROLES_FROM_USER,
           self::DELETED_USERS,
        ];
    }
}
