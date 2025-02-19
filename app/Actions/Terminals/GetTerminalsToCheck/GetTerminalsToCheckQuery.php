<?php

namespace App\Actions\Terminals\GetTerminalsToCheck;

use App\TerminalCheck;
use Carbon\Carbon;

final class GetTerminalsToCheckQuery
{
    public function get(): TerminalsToCheckViewModel
    {
        $terminalChecks = TerminalCheck::query()
            ->select([
                'terminal_checks.date_end_check',
                'terminals.hash_id as terminal_hash_id'
            ])
            ->leftJoin('terminals', 'terminal_checks.terminal_id', '=', 'terminals.id')
            ->get();

        $now = Carbon::now();

        $lessMonth = $terminalChecks
            ->filter(function (TerminalCheck $terminalCheck) use ($now) {
                if (!$terminalCheck->terminal_hash_id) {
                    return false;
                }

                if ($now->isSameDay($terminalCheck->date_end_check)) {
                    return true;
                }

                return $now->lessThanOrEqualTo($terminalCheck->date_end_check) &&
                    $now->diffInDays($terminalCheck->date_end_check) <= 30;
            })
            ->map(function (TerminalCheck $terminalCheck) {
                return $terminalCheck->terminal_hash_id;
            })
            ->values()
            ->toArray();

        $expired = $terminalChecks
            ->filter(function (TerminalCheck $terminalCheck) use ($now) {
                if (!$terminalCheck->terminal_hash_id) {
                    return false;
                }

                if ($now->isSameDay($terminalCheck->date_end_check)) {
                    return false;
                }

                return $now->greaterThan($terminalCheck->date_end_check);
            })
            ->map(function (TerminalCheck $terminalCheck) {
                return $terminalCheck->terminal_hash_id;
            })
            ->values()
            ->toArray();

        return new TerminalsToCheckViewModel($lessMonth, $expired);
    }
}
