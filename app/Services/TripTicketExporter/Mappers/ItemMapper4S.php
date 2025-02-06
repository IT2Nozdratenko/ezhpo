<?php

namespace App\Services\TripTicketExporter\Mappers;

use App\Enums\LogisticsMethodEnum;
use App\Enums\TransportationTypeEnum;
use App\Models\Forms\MedicForm;
use App\Models\Forms\TechForm;
use App\Models\TripTicket;
use App\Req;
use App\Services\TripTicketExporter\ViewModels\CarViewModel;
use App\Services\TripTicketExporter\ViewModels\CompanyViewModel;
use App\Services\TripTicketExporter\ViewModels\DriverViewModel;
use App\Services\TripTicketExporter\ViewModels\ExportedItem;
use App\Services\TripTicketExporter\ViewModels\ExportedItem4S;
use App\Services\TripTicketExporter\ViewModels\MedicFormViewModel;
use App\Services\TripTicketExporter\ViewModels\StampViewModel;
use App\Services\TripTicketExporter\ViewModels\TechFormViewModel;
use App\Services\TripTicketExporter\ViewModels\TripTicketViewModel;
use Illuminate\Support\Carbon;

final class ItemMapper4S implements ItemMapperInterface
{
    /**
     * @param TripTicket $tripTicket
     * @return ExportedItem4S
     */
    public function fromEloquent(TripTicket $tripTicket): ExportedItem
    {
        $tripTicketViewModel = new TripTicketViewModel(
            $tripTicket->ticket_number,
            $tripTicket->start_date
                ? Carbon::parse($tripTicket->start_date)
                : null,
            $tripTicket->period_pl
                ? Carbon::parse($tripTicket->period_pl)
                : null,
            $tripTicket->validity_period,
            LogisticsMethodEnum::fromString($tripTicket->logistics_method),
            TransportationTypeEnum::fromString($tripTicket->transportation_type)
        );

        $companyViewModel = $this->mapCompany($tripTicket);
        $driverViewModel = $this->mapDriver($tripTicket);
        $carViewModel = $this->mapCar($tripTicket);
        $medicFormViewModel = $this->mapMedic($tripTicket);
        $techFormViewModel = $this->mapTechForm($tripTicket);

        return new ExportedItem4S(
            $tripTicketViewModel,
            $companyViewModel,
            $driverViewModel,
            $carViewModel,
            $medicFormViewModel,
            $techFormViewModel
        );
    }

    private function mapCompany(TripTicket $tripTicket): ?CompanyViewModel
    {
        if (!$tripTicket->company) {
            return null;
        }

        $reqName = null;
        $req = Req::find($tripTicket->company->req_id);

        if ($req) {
            $reqName = $req->name;
        }

        $company = $tripTicket->company;

        return new CompanyViewModel(
            $company->name,
            $company->where_call,
            $reqName,
            $company->address,
            $company->ogrn
        );
    }

    private function mapDriver(TripTicket $tripTicket): ?DriverViewModel
    {
        if (!$tripTicket->driver) {
            return null;
        }

        $driverLicenseDate = null;
        if ($tripTicket->driver->driver_license_issued_at) {
            $driverLicenseDate = Carbon::parse($tripTicket->driver->driver_license_issued_at);
        }

        return new DriverViewModel(
            $tripTicket->driver->hash_id,
            $tripTicket->driver->fio,
            $tripTicket->driver->driver_license,
            $driverLicenseDate,
            $tripTicket->driver->snils,
        );
    }

    private function mapCar(TripTicket $tripTicket): ?CarViewModel
    {
        if (!$tripTicket->car) {
            return null;
        }

        return new CarViewModel(
            $tripTicket->car->hash_id,
            $tripTicket->car->gos_number,
            $tripTicket->car->mark_model,
            $tripTicket->car->official_type_auto ?? '',
        );
    }

    private function mapMedic(TripTicket $tripTicket): ?MedicFormViewModel
    {
        if (!$tripTicket->medicForm) {
            return null;
        }

        $form = $tripTicket->medicForm;

        /** @var MedicForm $details */
        $details = $form->details;
        $stamp = $details->getStamp();

        return new MedicFormViewModel(
            $form->date ? Carbon::parse($form->date) : null,
            $form->user ? $form->user->name : null,
            $stamp ? StampViewModel::fromStampOrDefault($stamp) : null
        );
    }

    private function mapTechForm(TripTicket $tripTicket): ?TechFormViewModel
    {
        if (!$tripTicket->techForm) {
            return null;
        }

        $form = $tripTicket->techForm;

        $odometer = null;
        $techForm = TechForm::where('forms_uuid', '=', $form->uuid)->first();

        if ($techForm) {
            $odometer = $techForm->odometer;
        }

        return new TechFormViewModel(
            $form->date ? Carbon::parse($form->date) : null,
            $form->user ? $form->user->name : null,
            $odometer,
        );
    }
}
