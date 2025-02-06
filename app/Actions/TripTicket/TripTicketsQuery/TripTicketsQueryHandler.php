<?php

namespace App\Actions\TripTicket\TripTicketsQuery;

use App\Models\TripTicket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

final class TripTicketsQueryHandler
{
    public function handle(TripTicketsQueryAction $action): Builder
    {
        if ($action->isTrash()) {
            $tripTickets = TripTicket::onlyTrashed();
        } else {
            $tripTickets = TripTicket::query();
        }

        $tripTickets = $tripTickets->select([
            'trip_tickets.id',
            'trip_tickets.uuid',
            'trip_tickets.ticket_number',
            'trip_tickets.start_date',
            'trip_tickets.period_pl',
            'trip_tickets.validity_period',
            'trip_tickets.medic_form_id',
            'trip_tickets.tech_form_id',
            'trip_tickets.logistics_method',
            'trip_tickets.transportation_type',
            'trip_tickets.template_code',
            'trip_tickets.photos',
            'trip_tickets.created_at',
            'trip_tickets.deleted_at',

            'companies.name as company_name',
            'drivers.fio as driver_name',
            'cars.gos_number as car_number',
            'users.name as deleted_user_name'
        ])
            ->leftJoin(
                'companies',
                'companies.hash_id',
                '=',
                'trip_tickets.company_id',
            )
            ->leftJoin(
                'drivers',
                'drivers.hash_id',
                '=',
                'trip_tickets.driver_id',
            )
            ->leftJoin(
                'cars',
                'cars.hash_id',
                '=',
                'trip_tickets.car_id',
            )
            ->leftJoin(
                'users',
                'users.id',
                '=',
                'trip_tickets.deleted_id'
            )
            ->orderBy($action->getOrderKey(), $action->getOrderBy());

        $dateFrom = isset($action->getFilterParams()['date_from'])
            ? Carbon::parse($action->getFilterParams()['date_from'])
            : Carbon::now()->subYears(10);
        $dateTo = isset($action->getFilterParams()['date_to'])
            ? Carbon::parse($action->getFilterParams()['date_to'])
            : Carbon::now()->addYears(10);

        if (count($action->getFilterParams()) > 0 && $action->isFilterActivated()) {
            foreach ($action->getFilterParams() as $filterKey => $filterValue) {
                if ($filterValue === null || in_array($filterKey, ['date_from', 'date_to'])) {
                    continue;
                }

                $tripTickets->where("trip_tickets.$filterKey", '=', $filterValue);
            }
        }

        return $tripTickets->where(function ($query) use ($dateFrom, $dateTo) {
            $query->where(function ($subQuery) use ($dateFrom, $dateTo) {
                $subQuery->whereNotNull('trip_tickets.start_date')
                    ->whereBetween('trip_tickets.start_date', [$dateFrom, $dateTo]);
            })->orWhere(function ($subQuery) use ($dateFrom, $dateTo) {
                $subQuery
                    ->whereNull('trip_tickets.start_date')
                    ->whereBetween('trip_tickets.period_pl', [
                        $dateFrom->format('Y-m'),
                        $dateTo->format('Y-m')
                    ]);
            });
        });
    }
}
