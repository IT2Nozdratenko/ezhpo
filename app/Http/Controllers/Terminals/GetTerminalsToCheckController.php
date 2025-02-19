<?php

namespace App\Http\Controllers\Terminals;

use App\Actions\Terminals\GetTerminalsToCheck\GetTerminalsToCheckQuery;

final class GetTerminalsToCheckController
{
    public function __invoke(GetTerminalsToCheckQuery $service)
    {
        $viewModel = $service->get();

        return response()->json([
            'less_month' => $viewModel->getLessMonth(),
            'expired' => $viewModel->getExpired(),
        ]);
    }
}