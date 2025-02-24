<?php

namespace App\Actions\TripTicket\StoreTripTicket;

use App\Actions\TripTicket\TripTicketNumberGenerator;
use App\Enums\TripTicketStatus;
use App\Models\TripTicket;
use App\ValueObjects\EntityId;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Support\Facades\Auth;

final class StoreTripTicketHandler extends TripTicketNumberGenerator
{
    /**
     * @throws Exception
     */
    public function handle(StoreTripTicketAction $action): array
    {
        $user = Auth::user();
        $tripTickets = [];

        DB::beginTransaction();

        foreach ($action->getItems() as $item) {
            if ($item->getTicketNumber() && $this->findSimilar($item->getTicketNumber(), $action->getCompanyId())) {
                DB::rollBack();
                throw new Exception("Путевой лист с номером {$item->getTicketNumber()} уже существует");
            }

            if (! $item->getStartDate() && $item->getPeriodPl() && ! $this->checkPeriod($item->getPeriodPl())) {
                DB::rollBack();
                throw new Exception("Неверный формат периода ПЛ {$item->getPeriodPl()}");
            }

            if ($item->getStartDate() && $item->getPeriodPl() && $item->getStartDate()->format('Y-m') !== $item->getPeriodPl()) {
                DB::rollBack();
                $period = Carbon::parse($item->getPeriodPl());
                throw new Exception("Период ПЛ {$period->format('m.Y')} не совпадает с месяцем начала действия {$item->getStartDate()->format('d.m.Y')}");
            }

            $id = EntityId::next()->getId();

            $tripTickets[] = TripTicket::create([
                'uuid' => $id,
                'ticket_number' => $item->getTicketNumber() ?: $this->getTicketNumber($id),
                'company_id' => $action->getCompanyId(),
                'start_date' => $item->getStartDate(),
                'period_pl' => $item->getStartDate()
                    ? $item->getStartDate()->format('Y-m')
                    : $item->getPeriodPl(),
                'validity_period' => $item->getValidityPeriod(),
                'driver_id' => $action->getDriverId(),
                'car_id' => $action->getCarId(),
                'logistics_method' => $item->getLogisticsMethod(),
                'transportation_type' => $item->getTransportationType(),
                'template_code' => $item->getTemplateCode(),
                'user_id' => $user->id,
                'status' => TripTicketStatus::CREATED,
            ]);
        }

        DB::commit();

        return $tripTickets;
    }

    private function findSimilar(string $number, string $companyId): bool
    {
        $similar = TripTicket::withTrashed()
            ->where('ticket_number', '=', $number)
            ->where('company_id', '=', $companyId)
            ->where('created_at', '>=', Carbon::now()->subYear())
            ->first();

        return $similar !== null;
    }

    private function checkPeriod(string $periodPl): bool
    {
        if (preg_match('/^\d{4}-\d{2}$/', $periodPl) !== 1) {
            return false;
        }

        $date = Carbon::createFromFormat('!Y-m', $periodPl);
        return $date && $date->format('Y-m') === $periodPl;
    }
}
