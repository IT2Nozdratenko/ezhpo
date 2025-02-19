<?php

namespace App\Http\Controllers\Terminals;

use App\Actions\Terminals\GetTerminalsConnectionStatus\GetTerminalsConnectionStatusParams;
use App\Actions\Terminals\GetTerminalsConnectionStatus\GetTerminalsConnectionStatusQuery;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetTerminalsConnectionStatusController
{
    public function __invoke(Request $request, GetTerminalsConnectionStatusQuery $query)
    {
        $statuses = $query->get(new GetTerminalsConnectionStatusParams(
            $request->input('terminals_ids')
        ));

        return response()->json($statuses)->setStatusCode(Response::HTTP_OK);
    }
}