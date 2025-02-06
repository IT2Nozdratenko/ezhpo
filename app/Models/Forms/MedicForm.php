<?php

namespace App\Models\Forms;

use App\Enums\FlagPakEnum;
use App\Enums\FormTypeEnum;
use App\Point;
use App\Stamp;
use App\User;
use App\ValueObjects\NotAdmittedReasons;
use App\ValueObjects\PressureLimits;
use App\ValueObjects\Pulse;
use App\ValueObjects\PulseLimits;
use App\ValueObjects\Temperature;
use App\ValueObjects\Tonometer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicForm extends Model
{
    protected $primaryKey = 'forms_uuid';

    protected $keyType = 'string';

    public $timestamps = false;

    public $fillable = [
        'forms_uuid',

        'day_hash',

        'type_view',

        'period_pl',
        'is_dop',
        'result_dop',

        'flag_pak',
        'operator_id',
        'terminal_id',

        'driver_group_risk',
        'pressure',
        'tonometer',
        't_people',
        'pulse',
        'proba_alko',
        'alcometer_mode',
        'alcometer_result',
        'test_narko',
        'med_view',
        'admitted',
        'complaint',
        'condition_visible_sliz',
        'condition_koj_pokr',
        'comments',

        'photos',
        'videos',

        'protokol_path',
        'closing_path'
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'forms_uuid', 'uuid');
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'terminal_id', 'id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id', 'id');
    }

    public function getNotAdmittedReasonsAttribute(): array
    {
        return NotAdmittedReasons::fromForm($this)->getReasons();
    }

    public function getDismissedReasonAttribute(): array
    {
        if (($this->attributes['is_dop'] ?? 0) === 1) {
            return [];
        }

        $result = [];
        $driver = $this->form->driver;

        if ($this->attributes['proba_alko'] === 'Положительно') {
            $result[] = 'алкоголь';
        }

        if (($this->attributes['test_narko'] !== 'Отрицательно') && ($this->attributes['test_narko'] !== 'Не проводился')) {
            $result[] = 'наркотики';
        }

        if ($this->attributes['med_view'] !== 'В норме') {
            $result[] = 'состояние здоровья';
        }

        $pressure = Tonometer::fromString($this->attributes['tonometer']);
        $pressureLimits = PressureLimits::create($driver);
        if (!$pressure->isAdmitted($pressureLimits)) {
            $result[] = 'давление';
        }

        $pulse = new Pulse(intval($this->attributes['pulse']));
        $pulseLimits = PulseLimits::create($driver);
        if (!$pulse->isAdmitted($pulseLimits)) {
            $result[] = 'повышенный пульс';
        }

        if (!(new Temperature(floatval($this->attributes['t_people'])))->isAdmitted()) {
            $result[] = 'повышенная температура';
        }

        return $result;
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

    public function getStamp(): ?Stamp
    {
        if ($this->getAttribute('flag_pak') !== FlagPakEnum::INTERNAL) {
            $terminal = $this->terminal;

            /** @var User $terminal */
            if ($terminal) {
                $terminalStamp = $terminal->getStamp();

                if ($terminalStamp) {
                    return $terminalStamp;
                }
            }
        }

        /** @var Point|null $point */
        $point = $this->form->point;
        if ($point) {
            return $point->getStamp();
        }

        return null;
    }
}
