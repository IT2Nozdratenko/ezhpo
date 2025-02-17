<?php

namespace App\Services\CheckUserRoles;

use App\Services\CheckUserRoles\Enums\RestorationDataType;
use App\User;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class CheckUserRolesRestoreService
{
    public function getAvailableLogsList(): array
    {
        $fileNames = Storage::disk('public')->files(CheckUserRolesLogsGenerator::LOGS_CATALOG);

        $fileNames = array_map(function ($item) {
            return str_replace(CheckUserRolesLogsGenerator::LOGS_CATALOG . '/', '', $item);
        }, $fileNames);

        rsort($fileNames);

        return $fileNames;
    }

    /**
     * @throws Exception
     */
    public function restore(string $fileName)
    {
        $path = CheckUserRolesLogsGenerator::LOGS_CATALOG . '/' . $fileName;

        try {
            $file = Storage::disk('public')->get($path);
        } catch (FileNotFoundException $e) {
            throw new Exception("Файл $path не найден");
        }

        $data = json_decode($file, true);

        $this->deleteUsers($data[RestorationDataType::CREATED_USERS]);
        $this->restoreUsers($data[RestorationDataType::DELETED_USERS]);

        $detachedRolesData = $data[RestorationDataType::DETACHED_ROLES_FROM_USER];
        foreach ($detachedRolesData as $userId => $roleIds) {
            $this->attachRoles($userId, $roleIds);
        }

        $this->createRoleRelations($data[RestorationDataType::DELETED_ROLE_RELATIONS]);
    }

    /**
     * @param int[] $ids
     * @return void
     * @throws Exception
     */
    private function deleteUsers(array $ids) {
        User::whereIn('id', $ids)->delete();
    }

    public function restoreUsers(array $ids)
    {
        User::withTrashed()->whereIn('id', $ids)->restore();
    }

    private function attachRoles(string $userId, array $roleIds) {
        User::withTrashed()->find($userId)->roles()->attach($roleIds);
    }

    private function createRoleRelations(array $relations) {
        DB::table('model_has_roles')
            ->insert($relations);
    }
}
