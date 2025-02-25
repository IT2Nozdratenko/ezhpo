<?php

namespace App\Actions\Anketa\GetFormVerificationDetails;

use App\Models\Forms\Form;
use App\Models\TripTicket;
use App\Services\TripTicket\GetTripTicketPhotos\GetTripTicketPhotosCommand;
use App\Services\TripTicket\GetTripTicketPhotos\GetTripTicketPhotosHandler;
use App\ViewModels\FormVerificationDetails\TripTicketDetails;

final class GetTripTicketDetailsQuery
{
    /**
     * @var GetTripTicketPhotosHandler
     */
    private $photosHandler;

    /**
     * @param GetTripTicketPhotosHandler $photosHandler
     */
    public function __construct(GetTripTicketPhotosHandler $photosHandler)
    {
        $this->photosHandler = $photosHandler;
    }

    public function get(Form $form): ?TripTicketDetails
    {
        $tripTicket = TripTicket::where('medic_form_id', '=', $form->id)->first();

        if ($tripTicket === null) {
            return null;
        }

        return new TripTicketDetails(
            $tripTicket,
            $this->photosHandler->handle(new GetTripTicketPhotosCommand($tripTicket))
        );
    }
}
