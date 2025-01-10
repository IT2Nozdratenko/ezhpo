<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Town extends Model
{
    use SoftDeletes;

    public $fillable
        = [
            'id',
            'hash_id',
            'name',
            'deleted_id',
            'deleted_at',
            'auto_created'
        ];

    public function pvs(): HasMany
    {
        return $this->hasMany(Point::class, 'pv_id');
    }

    //TODO: заменить трейтом
    public function delete()
    {
        $this->deleted_id = user()->id;
        $this->save();

        return parent::delete();
    }

    public function deleted_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_id', 'id')
                    ->withDefault();
    }

    public function getName($id): string
    {
        $id = explode(',', $id);

        $data = self::query()
            ->select([
                'name'
            ])
            ->whereIn('id', $id)
            ->get()
            ->pluck('name')
            ->toArray();

        return implode(', ', $data) ?? '';
    }
}
