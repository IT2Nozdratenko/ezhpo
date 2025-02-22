<?php

namespace App;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    public $fillable = [
        'hash_id',
        'related_user_id',
        'name',
        'official_name',
        'dismissed',
        'note',
        'comment',
        'procedure_pv',
        'user_id',
        'req_id',
        'pv_id',
        'town_id',
        'products_id',
        'where_call',
        'where_call_name',
        'inn',
        'kpp',
        'ogrn',
        'address',
        'required_type_briefing',
        'has_actived_prev_month',
        'bitrix_link',
        'document_bdd',
        'deleted_id',
        'pressure_systolic',
        'pressure_diastolic',
        'link_waybill',
        'auto_created',
        'deleted_at',
        'reqs_validated',
        'one_c_synced'
    ];

    public static function getAll()
    {
        $user = auth()->user();

        if ($user->hasRole('client')) {
            $companyId = User::getUserCompanyId();

            if ($companyId) {
                return self::find($companyId)->get();
            }
        }

        return self::all();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id', 'id');
    }

    public function point(): BelongsTo
    {
        return $this->belongsTo(Point::class, 'pv_id')
            ->withDefault();
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function deleted_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_id', 'id')
            ->withDefault();
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(
            Contract::class,
            'company_id',
            'id'
        );
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'company_id', 'id');
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class, 'company_id', 'id');
    }

    public function delete()
    {
        $this->deleted_id = user()->id;
        $this->save();

        return parent::delete();
    }

    public function getName($id, $field = 'id'): string
    {
        $company = Company::where($field, $id)->first();

        $companyName = '';
        if ($company) {
            $companyName = $company['name'];
        }

        return $companyName;
    }
}
