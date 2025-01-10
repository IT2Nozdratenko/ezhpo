<?php

namespace App;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use SoftDeletes;

    public $fillable = [
        'hash_id',
        'photo',
        'fio',
        'year_birthday',
        'phone',
        'gender',
        'group_risk',
        'company_id',
        'products_id',
        'count_pl',
        'note',
        'procedure_pv',
        'date_bdd',
        'date_prmo',
        'snils',
        'driver_license',
        'driver_license_issued_at',
        'date_driver_license',
        'date_narcotic_test',
        'date_report_driver',
        'time_card_driver',
        'town_id',
        'dismissed',
        'autosync_fields',
        'date_of_employment',
        'contract_id',
        'deleted_id',
        'pressure_systolic',
        'pressure_diastolic',
        'auto_created',
        'deleted_at'
    ];

    protected $casts = [
        'date_of_employment' => 'datetime',
    ];

    public function inspections_medic(): HasMany
    {
        return $this->hasMany(
            Anketa::class,
            'driver_id',
            'hash_id'
        )->where('type_anketa', 'medic');
    }

    public function inspections_pechat_pl(): HasMany
    {
        return $this->hasMany(
            Anketa::class,
            'driver_id',
            'hash_id'
        )->where('type_anketa', 'pechat_pl');
    }

    public function inspections_bdd(): HasMany
    {
        return $this->hasMany(
            Anketa::class,
            'driver_id',
            'hash_id'
        )->where('type_anketa', 'bdd');
    }

    public function inspections_report_cart(): HasMany
    {
        return $this->hasMany(
            Anketa::class,
            'driver_id',
            'hash_id'
        )->where('type_anketa', 'report_cart');
    }

    public function contracts(): BelongsToMany
    {
        return $this->belongsToMany(
            Contract::class,
            'driver_contact_pivot',
            'driver_id',
            'contract_id'
        );
    }

    public function deleted_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_id', 'id')
            ->withDefault();
    }

    //TODO: заменить трейтом
    public function delete()
    {
        $this->deleted_id = user()->id;
        $this->save();

        return parent::delete();
    }

    public function getAutoSyncFieldsFromHashId($hash_id)
    {
        return self::getAutoSyncFields($hash_id);
    }

    public static function getAutoSyncFields($hash_id)
    {
        $element = self::where('hash_id', $hash_id)->first();

        if ($element && $element->autosync_fields) {
            return explode(',', $element->autosync_fields);
        }

        return [];
    }

    public static function getName($id = 0): string
    {
        $driver = Driver::where('hash_id', $id)->first();

        if (!$driver) {
            return '';
        }

        return $driver->fio;
    }

    public function checkGroupRisk(
        string $tonometer = null,
        string $testDrugs = null,
        string $testAlko = null
    )
    {
        $currentYear = date('Y');
        $birthdayYear = date('Y', strtotime($this->getAttribute('year_birthday')));
        $age = $currentYear - $birthdayYear;
        $groupRisk = '';
        if ($age > 50) {
            $groupRisk = 'Возраст';
        }

        if ($tonometer > 140) {
            $groupRisk = 'А\Д';
        }

        if ($testDrugs === 'Положительно') {
            $groupRisk = 'Наркотики';
        }

        if ($testAlko === 'Положительно') {
            $groupRisk = 'Алкоголь';
        }

        $this->setAttribute('group_risk', $groupRisk);
        $this->save();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id')
            ->withDefault();
    }

    public function getPressureSystolic()
    {
        if ($this->pressure_systolic) {
            return $this->pressure_systolic;
        }

        if ($this->company->pressure_systolic) {
            return $this->company->pressure_systolic;
        }

        $setting = Settings::setting('pressure_systolic');

        if ($setting) {
            return $setting;
        }

        return 150;
    }

    public function getPressureDiastolic()
    {
        if ($this->pressure_diastolic) {
            return $this->pressure_diastolic;
        }

        if ($this->company->pressure_diastolic) {
            return $this->company->pressure_diastolic;
        }

        $setting = Settings::setting('pressure_diastolic');

        if ($setting) {
            return $setting;
        }

        return 100;
    }

    public function getPulseLower()
    {
        $setting = Settings::setting('pulse_lower');
        if ($setting) {
            return $setting;
        }

        return PHP_INT_MIN;
    }

    public function getPulseUpper()
    {
        $setting = Settings::setting('pulse_upper');
        if ($setting) {
            return $setting;
        }

        return PHP_INT_MAX;
    }

    public function getTimeOfAlcoholBan()
    {
        $setting = Settings::setting('time_of_alcohol_ban');

        if ($setting) {
            return $setting;
        }

        return 0;
    }

    public function getTimeOfPressureBan()
    {
        $setting = Settings::setting('time_of_pressure_ban');

        if ($setting) {
            return $setting;
        }

        return 0;
    }
}
