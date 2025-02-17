<?php

namespace App\Repositories\CheckUserRoles;

use App\User;
use Illuminate\Support\Facades\DB;

final class CheckUserRolesRepository
{
    public function findUsersWithDuplicatedRoles(): array
    {
        return DB::table('users as u')
            ->select('u.id')
            ->join('model_has_roles as mhr', 'u.id', '=', 'mhr.model_id')
            ->groupBy(['u.id', 'mhr.role_id'])
            ->havingRaw('COUNT(*) > 1')
            ->whereNull('u.deleted_at')
            ->get()
            ->toArray();
    }

    public function findRoleRelationsWithoutUser(): array
    {
        return DB::table('model_has_roles as mhr')
            ->select([
                'mhr.role_id',
                'mhr.model_id',
            ])
            ->leftJoin('users as u', 'mhr.model_id', '=', 'u.id')
            ->whereNull('u.id')
            ->get()
            ->toArray();
    }

    public function deleteRoleRelationsWithoutUser(array $roles, $batchSize = 1000)
    {
        $chunks = array_chunk($roles, $batchSize);

        foreach ($chunks as $chunk) {
            $roleIds = array_column($chunk, 'role_id');
            $modelIds = array_column($chunk, 'model_id');

            DB::table('model_has_roles')
                ->whereIn('role_id', $roleIds)
                ->whereIn('model_id', $modelIds)
                ->delete();
        }
    }

    public function findCompanies(): array
    {
        $data = DB::table('companies as c')
            ->select([
                'c.id',
                'c.hash_id',
                'c.name',
                'u.id as user_id',
                DB::raw("json_arrayagg(mhr.role_id) as role_ids"),
            ])
            ->leftJoin(
                'users as u',
                function ($join) {
                    $join->on('u.login', '=', DB::raw("CONCAT('0', c.hash_id)"))
                        ->whereNull('u.deleted_at');
                }
            )
            ->leftJoin('model_has_roles as mhr', 'mhr.model_id', '=', 'u.id')
            ->whereNull('c.deleted_at')
            ->groupBy(['c.id', 'c.hash_id', 'c.name', 'u.id'])
            ->get()
            ->toArray();

        return array_map(function ($item) {
            return (object)[
                'id' => $item->id,
                'hash_id' => $item->hash_id,
                'name' => $item->name,
                'user_id' => $item->user_id,
                'role_ids' => $this->extractRoles($item->role_ids),
            ];
        }, $data);
    }

    public function findDrivers(): array
    {
        $data = DB::table('drivers as d')
            ->select([
                'd.id',
                'd.hash_id',
                'd.company_id',
                'd.fio',
                'u.id as user_id',
                DB::raw("json_arrayagg(mhr.role_id) as role_ids"),
            ])
            ->leftJoin(
                'users as u',
                function ($join) {
                    $join->on('u.login', '=', 'd.hash_id')
                        ->whereNull('u.deleted_at');
                }
            )
            ->leftJoin('model_has_roles as mhr', 'mhr.model_id', '=', 'u.id')
            ->whereNull('d.deleted_at')
            ->groupBy(['d.id', 'd.hash_id', 'd.company_id', 'd.fio', 'u.id'])
            ->get()
            ->toArray();

        return array_map(function ($item) {
            return (object)[
                'id' => $item->id,
                'hash_id' => $item->hash_id,
                'company_id' => $item->company_id,
                'fio' => $item->fio,
                'user_id' => $item->user_id,
                'role_ids' => $this->extractRoles($item->role_ids),
            ];
        }, $data);
    }

    public function findUsers(): array
    {
        $data = DB::table('users as u')
            ->select([
                'u.id',
                'u.login',
                'c.id as company_id',
                'd.id as driver_id',
                DB::raw('json_arrayagg(mhr.role_id) as role_ids')
            ])
            ->leftJoin('model_has_roles as mhr', 'mhr.model_id', '=', 'u.id')
            ->leftJoin(
                'companies as c',
                function ($join) {
                    $join->on('u.login', '=', DB::raw("CONCAT('0', c.hash_id)"))
                        ->whereNull('c.deleted_at');
                }
            )
            ->leftJoin(
                'drivers as d',
                function ($join) {
                    $join->on('u.login', '=', 'd.hash_id')
                        ->whereNull('d.deleted_at');
                }
            )
            ->whereNull('u.deleted_at')
            ->groupBy(['u.id', 'u.login', 'c.id', 'd.id'])
            ->get()
            ->toArray();

        return array_map(function ($item) {
            return (object)[
                'id' => $item->id,
                'login' => $item->login,
                'company_id' => $item->company_id,
                'driver_id' => $item->driver_id,
                'role_ids' => $this->extractRoles($item->role_ids),
            ];
        }, $data);
    }

    private function extractRoles($rolesJson): array
    {
        if ($rolesJson === null) {
            return [];
        }

        $value = json_decode($rolesJson, true);

        return array_unique(array_filter($value, function ($item) {
            return $item !== null;
        }));
    }
}
