<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Terminal extends Model
{
    use SoftDeletes;

    public $fillable = [
        'hash_id',
        'related_user_id',
        'name',
        'blocked',
        'pv_id',
        'stamp_id',
        'last_connection_at',
        'auto_created',
        'deleted_at',
        'deleted_id',
    ];

    public function delete()
    {
        $this->deleted_id = user()->id;
        $this->save();

        return parent::delete();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'related_user_id', 'id');
    }

    public function pv(): BelongsTo
    {
        return $this->belongsTo(Point::class, 'pv_id')
            ->withDefault();
    }

    public function stamp(): BelongsTo
    {
        return $this->belongsTo(Stamp::class, 'stamp_id', 'id');
    }

    public function terminalDevices(): HasMany
    {
        return $this->hasMany(TerminalDevice::class, 'terminal_id');
    }

    public function terminalCheck(): HasOne
    {
        return $this->hasOne(TerminalCheck::class, 'terminal_id');
    }
}
