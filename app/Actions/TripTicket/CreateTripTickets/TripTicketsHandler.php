<?php

namespace App\Actions\TripTicket\CreateTripTickets;

use App\Actions\TripTicket\TripTicketNumberGenerator;
use App\Enums\FormTypeEnum;
use App\Enums\TripTicketStatus;
use App\Models\Forms\Form;
use App\Models\TripTicket;
use App\ValueObjects\EntityId;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

final class TripTicketsHandler extends TripTicketNumberGenerator
{
    /**
     * @throws Exception
     */
    public function handle(TripTicketsAction $action): array
    {
        $tripTicketIds = [];

        $medicForms = $this->getMedicForms($action);
        $techForms = $this->getTechForms($action);

        $groupedMedic = $this->groupForms($medicForms);
        $groupedTech = $this->groupForms($techForms);

        foreach ($groupedMedic as $date => $drivers) {
            foreach ($drivers as $driverId => $medicList) {
                foreach ($medicList as $medic) {
                    $tech = $groupedTech
                        ->get($date, collect())
                        ->get($driverId, collect())
                        ->first();

                    $tripTicketIds[] = $this->createTripTicket($action, $date, $medic, $tech);

                    if ($tech) {
                        $groupedTech[$date][$driverId] = $groupedTech[$date][$driverId]->filter(
                            function ($item) use ($tech) {
                                return $item->id !== $tech->id;
                            }
                        );
                    }
                }
            }
        }

        foreach ($groupedTech as $date => $drivers) {
            foreach ($drivers as $techList) {
                foreach ($techList as $tech) {
                    $tripTicketIds[] = $this->createTripTicket($action, $date, null, $tech);
                }
            }
        }

        return $tripTicketIds;
    }

    private function groupForms($forms)
    {
        return $forms->reduce(function ($carry, Form $form) {
            $date = Carbon::parse($form->date)->format('Y-m-d');

            if (! isset($carry[$date])) {
                $carry[$date] = collect();
            }

            if (! isset($carry[$date][$form->driver_id])) {
                $carry[$date][$form->driver_id] = collect();
            }

            $carry[$date][$form->driver_id][] = $form;

            return $carry;
        }, collect());
    }

    private function getMedicForms(TripTicketsAction $action)
    {
        return $this->getFormBuilder($action)
            ->join(
                'medic_forms',
                'medic_forms.forms_uuid',
                '=',
                'forms.uuid'
            )
            ->where('type_anketa', '=', FormTypeEnum::MEDIC)
            ->where('medic_forms.admitted', '=', 'Допущен')
            ->whereDoesntHave('tripTicketMedic')
            ->get();
    }

    private function getTechForms(TripTicketsAction $action)
    {
        return $this->getFormBuilder($action)
            ->join(
                'tech_forms',
                'tech_forms.forms_uuid',
                '=',
                'forms.uuid'
            )
            ->where('type_anketa', '=', FormTypeEnum::TECH)
            ->where('tech_forms.point_reys_control', '=', 'Пройден')
            ->whereNotNull('tech_forms.car_id')
            ->whereDoesntHave('tripTicketTech')
            ->get();
    }

    private function getFormBuilder(TripTicketsAction $action)
    {
        return Form::where('forms.company_id', '=', $action->getCompany()->hash_id)
            ->when($action->getDriver(), function (Builder $query) use ($action) {
                $query->where('forms.driver_id', '=', $action->getDriver()->hash_id);
            })
            ->whereNotNull('forms.driver_id')
            ->whereBetween('forms.date', [$action->getDateFrom(), $action->getDateTo()->endOfDay()]);
    }

    private function createTripTicket(TripTicketsAction $action, string $date, Form $medicForm = null, Form $techForm = null): TripTicket
    {
        $carId = $techForm
            ? $techForm->details->car_id
            : null;
        $driverId = $medicForm
            ? $medicForm->driver_id
            : $techForm->driver_id;
        $id = EntityId::next()->getId();

        return TripTicket::create([
            'uuid' => $id,
            'ticket_number' => $this->getTicketNumber($id),
            'company_id' => $action->getCompany()->hash_id,
            'start_date' => $date,
            'period_pl' => Carbon::parse($date)->format('Y-m'),
            'validity_period' => $action->getValidityPeriod(),
            'medic_form_id' => $medicForm ? $medicForm->id : null,
            'driver_id' => $action->getDriver() ? $action->getDriver()->hash_id : $driverId,
            'tech_form_id' => $techForm ? $techForm->id : null,
            'car_id' => $carId,
            'logistics_method' => $action->getLogisticsMethod(),
            'transportation_type' => $action->getTransportationType(),
            'template_code' => $action->getTemplateCode(),
            'user_id' => Auth::user()->id,
            'status' => TripTicketStatus::CREATED,
        ]);
    }
}
