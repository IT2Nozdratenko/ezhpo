<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TerminalDevice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'terminal_id',
        'device_name',
        'device_serial_number',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
