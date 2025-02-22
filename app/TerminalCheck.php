<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TerminalCheck extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'terminal_id',
        'serial_number',
        'date_check',
        'date_end_check'
    ];

    protected $casts = [
        'date_check' => 'date',
        'date_end_check' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
