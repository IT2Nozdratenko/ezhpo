<?php

namespace App\Actions\TripTicket\UpdateTripTicketPhotos;

use App\Enums\TripTicketStatus;
use App\Events\TripTickets\ChangeTripTicketStatus;
use Illuminate\Http\UploadedFile;
use Ramsey\Uuid\Uuid;
use Storage;

final class UpdateTripTicketPhotosHandler
{
    public function handle(UpdateTripTicketPhotosAction $action)
    {
        $files = $this->storeFiles($action->getPhotos());

        $action->getTripTicket()
            ->update([
                'photos' => array_merge($files, $action->getTripTicket()->photos ?: [])
            ]);

        event(new ChangeTripTicketStatus($action->getTripTicket(), TripTicketStatus::activated()));
    }

    private function removeFiles(array $files)
    {
        foreach ($files as $file) {
            Storage::disk('public')->delete($file['path']);
        }
    }

    private function storeFiles(array $files): array
    {
        $processedFiles = [];

        /** @var UploadedFile $file */
        foreach ($files as $file) {
            $uuid = Uuid::uuid4()->toString();
            $extension = $file->getClientOriginalExtension();
            $filename = $file->getClientOriginalName();
            $path = Storage::disk('public')->putFileAs('trip-tickets', $file, $uuid.'.'.$extension);

            $processedFiles[] = [
                'original_name' => $filename,
                'path' => $path,
            ];
        }

        return $processedFiles;
    }
}
