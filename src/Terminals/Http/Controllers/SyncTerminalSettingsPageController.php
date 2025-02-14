<?php
declare(strict_types=1);

namespace Src\Terminals\Http\Controllers;

use Illuminate\Http\Request;
use Src\Terminals\Queries\GetSyncPageQuery\GetSyncPageHandler;
use Src\Terminals\Queries\GetSyncPageQuery\GetSyncPageQuery;

final class SyncTerminalSettingsPageController
{
    public function __invoke(Request $request, GetSyncPageHandler $handler)
    {
        $rawIds = $request->input('terminal_ids', '');
        $terminalIds = null;
        if ($rawIds !== null) {
            $terminalIds = explode(',', $rawIds);
            if (empty($terminalIds)) {
                abort(400);
            }
        }

        $response = $handler->handle(new GetSyncPageQuery($terminalIds));

        return view('terminals::sync-settings', [
            'response' => $response,
        ]);
    }
}
