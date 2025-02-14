<?php
declare(strict_types=1);

namespace Src\Terminals\Queries\GetSyncPageQuery;

use App\User;
use Src\Terminals\Eloquent\TerminalSettings;
use Src\Terminals\Factories\SettingsFactory;
use Src\Terminals\ValueObjects\Settings;

final class GetSyncPageHandler
{
    public function handle(GetSyncPageQuery $query): GetSyncPageResponse
    {
        $terminals = [];
        $firstTerminalId = null;
        if ($query->getTerminalIds() !== null) {
            $terminals = User::query()
                ->select([
                    'users.id',
                    'users.hash_id',
                    'terminal_checks.serial_number as serial_number',
                    'users.name'
                ])
                ->leftJoin('terminal_checks', 'users.id', '=', 'terminal_checks.user_id')
                ->leftJoin('model_has_roles', function ($join) {
                    $join->on('users.id', '=', 'model_has_roles.model_id')
                        ->where('model_has_roles.role_id', '=', 9);
                })
                ->whereNotNull('model_has_roles.model_id')
                ->whereIn('users.id', $query->getTerminalIds())
                ->get()
                ->map(function ($model) {
                    return new TerminalViewModel(
                        $model->id,
                        sprintf(
                            '[%s] %s %s',
                            $model->hash_id,
                            $model->name,
                            $model->serial_number ? "s/n: " . $model->serial_number : ""
                        )
                    );
                })
                ->toArray();
            $firstTerminalId = $query->getTerminalIds()[0];
        }

        $settings = optional(TerminalSettings::settingsForTerminal($firstTerminalId)->first())->settings;

        if ($settings === null) {
            $settings = new Settings(
                SettingsFactory::makeMain(),
                SettingsFactory::makeSystem(),
            );
        }

        return new GetSyncPageResponse(
            $terminals,
            $settings
        );
    }
}
