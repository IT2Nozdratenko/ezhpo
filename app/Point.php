<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Point extends Model
{
    use SoftDeletes;

    public $fillable
        = [
            'hash_id',
            'name',
            'pv_id',
            'company_id',
            'deleted_at',
            'auto_created'
        ];

    public static function getPointText($id = 0)
    {
        /** @var Point $point */
        $point = Point::find($id);

        if ($point) {
            return $point->name;
        }

        return '';
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

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class, 'pv_id');
    }

    public static function getAll(): array
    {
        return Town::query()
            ->select([
                'towns.id',
                'towns.name'
            ])
            ->with(['pvs'])
            ->get()
            ->toArray();
    }

}
