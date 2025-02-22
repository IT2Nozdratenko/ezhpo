<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Employee extends Model
{
    use SoftDeletes;

    public $fillable = [
        'hash_id',
        'related_user_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id', 'id');
    }

    public function whoDeleted(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_id', 'id');
    }

    public function pv(): BelongsTo
    {
        return $this->belongsTo(Point::class, 'pv_id')->withDefault();
    }

    public function points(): BelongsToMany
    {
        return $this->belongsToMany(Point::class, 'points_to_employees', 'employee_id', 'point_id');
    }
}
