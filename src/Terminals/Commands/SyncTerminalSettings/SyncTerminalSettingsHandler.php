<?php
declare(strict_types=1);

namespace Src\Terminals\Commands\SyncTerminalSettings;

use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Src\Terminals\Eloquent\TerminalSettings;

final class SyncTerminalSettingsHandler
{
    public function handle(SyncTerminalSettingsCommand $command): void
    {
        $settingsJson = json_encode($command->getTerminalSettings()->toArray(), JSON_THROW_ON_ERROR);

        if (count($command->getTerminalIds()) === 0 ) {
            TerminalSettings::query()->where('id', '=', Uuid::NIL)->update([
                'settings' => $settingsJson,
            ]);
            return;
        }

        foreach ($command->getTerminalIds() as $terminalId) {
            TerminalSettings::updateOrInsert(
                ['terminal_id' => $terminalId],
                [
                    'id' => Uuid::uuid4()->toString(),
                    'settings' => $settingsJson,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

    }
}
