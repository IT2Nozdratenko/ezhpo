<?php

use App\Role;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Contracts\Permission as PermissionContract;

class CreateUpdatePressureFieldsPermissions extends Migration
{
    const PERMISSIONS = [
        'drivers_update_pressure_fields' => 'Водители - Редактирование порогов давления',
        'company_update_pressure_fields' => 'Компании - Редактирование порогов давления',
    ];

    const ROLES = [
        'admin',
        'manager'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     * @throws Exception
     */
    public function up()
    {
        /** @var Role $role */
        $roles = Role::query()
            ->whereIn('name', self::ROLES)
            ->get();

        if ($roles->count() !== count(self::ROLES)) {
            throw new Exception('Some roles not found!');
        }

        /** @var User $user */
        $user = User::query()
            ->withoutGlobalScopes()
            ->where('login', User::DEFAULT_USER_LOGIN)
            ->first();

        if (empty($user)) {
            throw new Exception('Default admin user does not exists!');
        }

        $permissionClass = app(PermissionContract::class);

        foreach (self::PERMISSIONS as $slug => $title) {
            $permission = $permissionClass::findOrCreate($slug, $title);

            $permissionId = $permission->id;

            $roles->each(function ($role) use ($permissionId) {
                $role->permissions()->syncWithoutDetaching([$permissionId]);
            });

            $user->permissions()->syncWithoutDetaching([$permissionId]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permissionClass = app(PermissionContract::class);

        foreach (self::PERMISSIONS as $slug => $title) {
            $permission = $permissionClass::findOrCreate($slug, $title);

            $permission->forceDelete();
        }
    }
}
