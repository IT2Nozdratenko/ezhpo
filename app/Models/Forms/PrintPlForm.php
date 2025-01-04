<?php

namespace App\Models\Forms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintPlForm extends Model
{
    protected $primaryKey = 'forms_uuid';

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'forms_uuid',

        'count_pl',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'forms_uuid', 'uuid');
    }
}
