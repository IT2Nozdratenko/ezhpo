<?php

namespace App;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use SoftDeletes;

    public $fillable
        = [
            'hash_id',
            'gos_number',
            'mark_model',
            'type_auto',
            'products_id',
            'trailer',
            'company_id',
            'count_pl',
            'note',
            'procedure_pv',
            'date_prto',
            'date_techview',
            'time_skzi',
            'date_osago',
            'town_id',
            'dismissed',
            'autosync_fields',
            'contract_id',
            'deleted_id',
            'auto_created',
            'deleted_at'
        ];

    public function inspections_tech(): HasMany
    {
        return $this
            ->hasMany(Anketa::class, 'car_id', 'hash_id')
            ->where('type_anketa', 'tech');
    }

    public function deleted_user(): BelongsTo
    {
        return $this
            ->belongsTo(User::class, 'deleted_id', 'id')
            ->withDefault();
    }

    public function contracts(): BelongsToMany
    {
        return $this->belongsToMany(
            Contract::class,
            'car_contact_pivot',
            'car_id',
            'contract_id'
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    //TODO: заменить трейтом
    public function delete()
    {
        $this->deleted_id = user()->id;
        $this->save();

        return parent::delete();
    }

    public static function getAutoSyncFields($hash_id): array
    {
        $element = self::where('hash_id', $hash_id)->first();

        if (!$element) {
            return [];
        }

        if ($element->autosync_fields) {
            return explode(',', $element->autosync_fields) ?? [];
        }

        return [];
    }

    public function getAutoSyncFieldsFromHashId($hash_id): array
    {
        return self::getAutoSyncFields($hash_id);
    }

    public static function getName($id = 0): string
    {
        $car = self::where('hash_id', $id)->first();

        if (!$car) {
            return '';
        }

        return $car->gos_number;
    }
}
