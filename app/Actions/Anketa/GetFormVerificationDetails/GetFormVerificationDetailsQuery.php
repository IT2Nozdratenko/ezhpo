<?php

namespace App\Actions\Anketa\GetFormVerificationDetails;

use App\Enums\FormLabelingType;
use App\Exceptions\ExpiredFormPeriodPlException;
use App\Models\Forms\Form;
use App\Models\TripTicket;
use App\ViewModels\FormVerificationDetails\FormVerificationDetails;
use Carbon\Carbon;
use Http\Client\Common\Exception\HttpClientNotFoundException;

final class GetFormVerificationDetailsQuery
{
    /**
     * @var GetTripTicketDetailsQuery
     */
    private $tripTicketQuery;

    /**
     * @param GetTripTicketDetailsQuery $tripTicketQuery
     */
    public function __construct(GetTripTicketDetailsQuery $tripTicketQuery)
    {
        $this->tripTicketQuery = $tripTicketQuery;
    }

    /**
     * @throws ExpiredFormPeriodPlException
     */
    public function get(GetFormVerificationDetailsParams $params): FormVerificationDetails
    {
        $form = Form::withTrashed()->where('uuid', '=', $params->getFormUuid())->first();

        if (!$form) {
            throw new HttpClientNotFoundException();
        }

        $formNumber = null;
        $companyName = null;
        $formDate = null;
        $formPeriod = null;
        $driverName = null;
        $carGosNumber = null;
        $tripTicketDetails = null;

        if ($form->deleted_at !== null) {
            $verified = false;
        }
        else {
            $verified = true;

            $prefixes = [
                FormLabelingType::MEDIC => 'МО',
                FormLabelingType::TECH => 'ТО',
            ];
            $formNumber = $prefixes[$form->type_anketa] . '-' . $form->id;

            if ($form->company && $form->company->name) {
                $companyName = $form->company->name;
            }

            if ($form->driver && $form->driver->fio) {
                $driverName = $form->driver->fio;
            }

            if ($form->date) {
                $formDate = Carbon::parse($form->date);
            }
            else if ($form->details->period_pl) {
                $now = Carbon::now();
                $formPeriod = Carbon::parse($form->details->period_pl);

                if ($formPeriod->year < $now->year || ($formPeriod->year === $now->year && $formPeriod->month < $now->month)) {
                    throw new ExpiredFormPeriodPlException();
                }
            }

            if ($form->details->car && $form->details->car->gos_number) {
                $carGosNumber = $form->details->car->gos_number;
            }

            $tripTicketDetails = $this->tripTicketQuery->get($form);
        }

        return new FormVerificationDetails(
            $verified,
            $params->getFormUuid(),
            $form->id,
            FormLabelingType::fromString($form->type_anketa),
            $formNumber,
            $companyName,
            $formDate,
            $formPeriod,
            $driverName,
            $carGosNumber,
            $tripTicketDetails
        );
    }
}
