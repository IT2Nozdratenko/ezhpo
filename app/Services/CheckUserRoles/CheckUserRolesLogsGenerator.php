<?php

namespace App\Services\CheckUserRoles;

use App\Services\CheckUserRoles\Enums\RestorationDataType;
use Storage;

final class CheckUserRolesLogsGenerator
{
    const LOGS_CATALOG = 'check-user-roles-logs';

    private $changes = [
        RestorationDataType::CREATED_USERS => [],
        RestorationDataType::DETACHED_ROLES_FROM_USER => [],
        RestorationDataType::DELETED_USERS => [],
        RestorationDataType::DELETED_ROLE_RELATIONS => [],
    ];

    /**
     * @param RestorationDataType $type
     * @param string|number|array $value
     * @return void
     */
    public function putValue(RestorationDataType $type, $value): void
    {
        $this->changes[$type->value()][] = $value;
    }

    /**
     * @param RestorationDataType $type
     * @param string $key
     * @param string|number|array $value
     * @return void
     */
    public function putByKey(RestorationDataType $type, string $key, $value): void
    {
        $this->changes[$type->value()][$key] = $value;
    }

    public function generate()
    {
        $json = json_encode($this->changes);
        $fileName = self::LOGS_CATALOG . '/logs_' . date('Y-m-d_H-i-s') . '.json';

        Storage::disk('public')->put($fileName, $json);
    }
}
