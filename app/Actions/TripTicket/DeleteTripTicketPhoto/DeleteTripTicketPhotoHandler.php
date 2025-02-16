<?php

namespace App\Actions\TripTicket\DeleteTripTicketPhoto;

use Illuminate\Support\Facades\Storage;

final class DeleteTripTicketPhotoHandler
{
    public function handle(DeleteTripTicketPhotoAction $action)
    {
        $files = $action->getTripTicket()->photos;

        $this->removeFile($action->getFilePath());
        $files = $this->updateFileArray($files, $action->getFilePath());

        $action->getTripTicket()
            ->update([
                'photos' => $files ?: []
            ]);
    }

    private function removeFile(string $path)
    {
        Storage::disk('public')->delete($path);
    }

    private function updateFileArray(array $files, string $path)
    {
        return array_reduce($files, function ($carry, $file) use ($path) {
            if ($file['path'] !== $path) {
                $carry[] = $file;
            }

            return $carry;
        });
    }
}
