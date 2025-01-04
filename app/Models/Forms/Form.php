<?php

namespace App\Models\Forms;

use App\Company;
use App\Driver;
use App\Enums\FormTypeEnum;
use App\Point;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;

class Form extends Model
{
    use SoftDeletes;

    public static $related = [
        FormTypeEnum::MEDIC => MedicForm::class,
        FormTypeEnum::PAK_QUEUE => MedicForm::class,
        FormTypeEnum::PAK => MedicForm::class,
        FormTypeEnum::TECH => TechForm::class,
        FormTypeEnum::BDD => BddForm::class,
        FormTypeEnum::PRINT_PL => PrintPlForm::class,
        FormTypeEnum::REPORT_CARD => ReportCartForm::class
    ];

    public static $relatedTables = [
        FormTypeEnum::MEDIC => 'medic_forms',
        FormTypeEnum::PAK_QUEUE => 'medic_forms',
        FormTypeEnum::PAK => 'medic_forms',
        FormTypeEnum::TECH => 'tech_forms',
        FormTypeEnum::BDD => 'bdd_forms',
        FormTypeEnum::PRINT_PL => 'print_pl_forms',
        FormTypeEnum::REPORT_CARD => 'report_cart_forms'
    ];

    public $fillable
        = [
            'id',
            'uuid',
            'type_anketa',
            'date',
            'created_at',
            'deleted_at',
            'updated_at',
            'deleted_id',
            'user_id',
            'user_eds',
            'user_validity_eds_start',
            'user_validity_eds_end',
            'driver_id',
            'point_id',
            'company_id',
            'realy'
        ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = $model->uuid ?? Uuid::uuid4();
        });
    }

    /**
     * @return HasOne|null
     */
    public function details(): ?HasOne
    {
        $formType = $this->getAttribute('type_anketa');
        if ($formType === null) {
            return null;
        }

        $related = self::$related[$formType] ?? null;
        if ($related === null) {
            return null;
        }

        return $this->hasOne($related, 'forms_uuid', 'uuid');
    }

    public function point(): BelongsTo
    {
        return $this->belongsTo(Point::class, 'point_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function deleted_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_id', 'id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'hash_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'hash_id');
    }

    public static function pakQueueCount(User $user): int
    {
        return self::query()
            ->pakQueueByUser($user)
            ->where('forms.date', '>=', Carbon::now()->subDay())
            ->count();
    }

    public function scopePakQueueByUser($query, User $user)
    {
        $query->where('forms.type_anketa', FormTypeEnum::PAK_QUEUE);

        if ($user->access('approval_queue_view_all')) {

        } else if ($user->hasRole('head_operator_sdpo')) {
            $query->join('points_to_users', function ($join) use ($user) {
                $join->on('forms.point_id', '=', 'points_to_users.point_id')
                    ->where('points_to_users.user_id', '=', $user->id);
            });
        } else {
            $query->where('forms.user_id', $user->id);
        }
    }
}
