<?php
declare(strict_types=1);

namespace Src\Terminals\Eloquent;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Src\Terminals\Factories\SettingsFactory;
use Src\Terminals\ValueObjects\Settings;

/**
 * @property string $id
 * @property int $terminal_id
 * @property Settings|null $settings
 * @property bool $is_synced
 * @method static Builder settingsForTerminal(int $terminalId)
 */
final class TerminalSettings extends Model
{
    use HasTimestamps, HasUuid;

    protected $table = 'terminal_settings';
    protected $guarded = ['id'];

    protected $casts = [
        'is_synced' => 'boolean',
    ];

    public function getSettingsAttribute($value): ?Settings
    {
        $rawSettings = json_decode($value, true);

        if (!is_array($rawSettings)) {
            return null;
        }

        return new Settings(
            SettingsFactory::makeMain($rawSettings['main'] ?? []),
            SettingsFactory::makeSystem($rawSettings['system'] ?? [])
        );
    }

    /**
     * @throws \JsonException
     */
    public function setSettingsAttribute(Settings $value): void
    {
        $this->attributes['settings'] = json_encode($value->toArray(), JSON_THROW_ON_ERROR);
    }

    public function scopeSettingsForTerminal(Builder $query, ?int $terminalId)
    {
        $query->where('terminal_id', '=', $terminalId)
            ->orWhereNull('terminal_id')
            ->orderByRaw('CASE WHEN terminal_id IS NULL THEN 1 ELSE 0 END, terminal_id desc');
    }
}
