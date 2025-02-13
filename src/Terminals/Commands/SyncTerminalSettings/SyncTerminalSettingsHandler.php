<?php
declare(strict_types=1);

namespace Src\Terminals\Commands\SyncTerminalSettings;

use Illuminate\Support\Facades\DB;

final class SyncTerminalSettingsHandler
{
    public function handle(SyncTerminalSettingsCommand $command): void
    {
        $settingsJson = json_encode($command->getTerminalSettings()->toArray(), JSON_THROW_ON_ERROR);

        $values = [];
        $bindings = [];

        foreach ($command->getTerminalId() as $terminalId) {
            $values[] = "(?, ?)";
            $bindings[] = $terminalId;
            $bindings[] = $settingsJson;
        }

        $valuesString = implode(',', $values);

        $sql = "
            INSERT INTO terminal_settings (terminal_id, settings)
            VALUES {$valuesString}
            ON DUPLICATE KEY UPDATE settings = VALUES(settings)
        ";

        DB::statement($sql, $bindings);
    }
}
