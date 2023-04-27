<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Town extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    public $fillable
        = [
            'id',
            'hash_id',
            'name',
            'deleted_id',
        ];

    public function pvs()
    {
        return $this->hasMany(Point::class, 'pv_id');
    }


    public function delete()
    {
        $this->deleted_id = user()->id;
        $this->save();

        return parent::delete(); // TODO: Change the autogenerated stub
    }

    public function deleted_user()
    {
        return $this->belongsTo(User::class, 'deleted_id', 'id')
                    ->withDefault();
    }

    public static function getName($id)
    {
        $id = explode(',', $id);

        $data = self::whereIn('id', $id)->get();

        if ( !$data) {
            $data = '';
        } else {
            $newData = '';

            foreach ($data as $dataItemKey => $dataItem) {
                $newData .= ($dataItemKey !== 0 ? ', ' : '').$dataItem->name;
            }

            $data = $newData;
        }

        return $data;
    }

    public static function getAll()
    {
        return self::all();
    }
}
