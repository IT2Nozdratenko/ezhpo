<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Request;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $name
 * @property Collection $companies
 */
class User extends Authenticatable
{
    use Notifiable, HasRoles, SoftDeletes;

    const DEFAULT_USER_LOGIN = 'it@nozdratenko.ru';

    public $fillable = [
            'hash_id',
            'req_id',
            'photo',
            'name',
            'email',
            'password',
            'eds',
            'pv_id',
            'timezone',
            'role',
            'role_manager',
            'blocked',
            'pv_id_default',
            'api_token',
            'login',
            'user_post',
            'company_id',
            'deleted_id',
            'last_connection_at',
            'stamp_id',
            'validity_eds_start',
            'validity_eds_end',
            'accepted_agreement',
            'deleted_at',
            'auto_created'
        ];

    protected $hidden = [
            'password',
        ];

    protected $casts = [
            'email_verified_at' => 'datetime',
            'last_connection_at' => 'datetime',
        ];

    public static $newUserRolesText = [
            1 => 'Контролёр ТС',
            2 => 'Медицинский сотрудник',
            3 => 'Водитель',
            4 => 'Оператор СДПО',
            5 => 'Менеджер',
            6 => 'Клиент',
            7 => 'Инженер БДД',
            8 => 'Администратор',
            9 => 'Терминал',
        ];

    public static $newUserRolesTextEN = [
            1 => 'tech',
            2 => 'medic',
            3 => 'driver',
            4 => 'operator_sdpo',
            5 => 'manager',
            6 => 'client',
            7 => 'engineer_bdd',
            8 => 'admin',
            9 => 'terminal',
        ];

    public static $userRolesValues = [
            'client' => 12,
            'tech' => 1,
            'medic' => 2,
            'driver' => 3,
            'terminal' => 778,
            'engineer_bdd' => 12,
            'manager' => 11,
            'admin' => 777,
            'operator_pak' => 4,
        ];

    public static $userRolesKeys = [
            '0' => 'medic',
            '1' => 'tech',
            '2' => 'medic',
            '4' => 'pak_queue',
            '12' => 'medic',
            '11' => 'medic',
            '777' => 'medic',
            '778' => 'medic',
        ];

    public static $userRolesText = [
            1 => 'Контролёр ТС',
            2 => 'Медицинский сотрудник',
            3 => 'Водитель',
            4 => 'Оператор СДПО',
            11 => 'Менеджер',
            12 => 'Клиент',
            13 => 'Инженер БДД',
            777 => 'Администратор',
            778 => 'Терминал',
        ];

    public function deleted_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_id', 'id')
            ->withDefault();
    }

    public function delete()
    {
        $this->deleted_id = user()->id;
        $this->save();

        return parent::delete();
    }

    public function roles($deleted = false): BelongsToMany
    {
        return $this->belongsToMany(Role::class,
            'model_has_roles',
            'model_id',
            'role_id',
            'id',
            'id'
        )->withPivot('deleted')
            ->wherePivot('deleted', $deleted ? 1 : 0);
    }

    public function anketas(): HasMany
    {
        return $this->hasMany(Anketa::class, 'user_id', 'id')
            ->withDefault();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id')
            ->withDefault();
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function pv(): BelongsTo
    {
        return $this->belongsTo(Point::class, 'pv_id')
            ->withDefault();
    }

    public function points(): BelongsToMany
    {
        return $this->belongsToMany(Point::class, 'points_to_users', 'user_id', 'point_id');
    }

    public function terminalDevices(): HasMany
    {
        return $this->hasMany(TerminalDevice::class, 'user_id');
    }

    public function terminalCheck(): HasOne
    {
        return $this->hasOne(TerminalCheck::class, 'user_id');
    }

    public function access(...$permissionName): bool
    {
        return $this->getAllPermissions()
            ->whereIn('name', $permissionName)
            ->isNotEmpty();
    }

    public static function getUserCompanyId($field = 'id', $withUserCompanyId = false): int
    {
        /** @var User $authUser */
        $authUser = auth()->user();
        $point = $authUser->pv_id;
        $point = Point::find($point);

        if ($point) {
            $company = $point->company_id ? Company::find($point->company_id) : 0;

            if ($company) {
               return $company->$field;
            }

        }

	    if ($withUserCompanyId && $authUser->company_id !== null) {
            $company = Company::find($authUser->company_id);

            if (! $company) {
                return -1;
            }

            return $company->$field;
        }

        return -1;
    }

    public function stamp(): BelongsTo
    {
        return $this->belongsTo(Stamp::class, 'stamp_id', 'id');
    }

    /**
     * Получение имени юзера
     *
     * @param int $id
     * @param bool $authId
     * @return string
     */
    public function getName($id = -1, $authId = true)
    {
        $id = $id ?: ($authId ? auth()->user()->id : -1);

        $userName = User::find($id);

        if ($userName) {
            $userName = $userName->name;
        } else {
            $userName = '';
        }

        return $userName;
    }
}
