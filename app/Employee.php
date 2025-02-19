<?php

namespace App;

use App\Enums\UserEntityType;
use App\Traits\HasUserRelation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Employee extends Model
{
    use SoftDeletes, HasUserRelation;

    const ENTITY_TYPE = UserEntityType::EMPLOYEE;

    public $fillable = [
        'hash_id',
        'name',
        'blocked',
        'pv_id',
        'eds',
        'validity_eds_start',
        'validity_eds_end',
        'auto_created',
        'deleted_at',
        'deleted_id',
    ];

    public function delete(): ?bool
    {
        $this->deleted_id = user()->id;
        $this->save();

        return parent::delete();
    }

    public function whoDeleted(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_id', 'id');
    }

    public function pv(): BelongsTo
    {
        return $this->belongsTo(Point::class, 'pv_id')
            ->withDefault();
    }
}
