<?php

namespace App\Actions\Anketa;

use App\Driver;
use App\Enums\FormLogActionTypesEnum;
use App\Enums\FormTypeEnum;
use App\Events\Forms\FormAction;
use App\Models\Forms\Form;
use App\Models\TripTicket;
use App\User;
use Illuminate\Support\Carbon;
use LogicException;

final class TrashFormHandler
{
    public function handle(Form $form, $action, User $user)
    {
        $this->checkThatNotBlockedToTrash($form);

        $form->deleted_id = $user->id;

        if (!$action) {
            $form->deleted_at = null;
        } else {
            if ($form->type_anketa === FormTypeEnum::MEDIC && $form->driver_id) {
                $this->disableBanIfNeed($form);
            }

            $form->deleted_at = Carbon::now();
        }

        $eventType = ! $action
            ? FormLogActionTypesEnum::RESTORING
            : FormLogActionTypesEnum::DELETING;

        event(new FormAction($user, $form, $eventType));

        $form->save();
    }

    /**
     * @throws LogicException
     */
    protected function checkThatNotBlockedToTrash(Form $form)
    {
        $attachedToTripTicket = TripTicket::query()
            ->where('medic_form_id', $form->id)
            ->orWhere('tech_form_id', $form->id)
            ->exists();

        if ($attachedToTripTicket) {
            throw new LogicException('Осмотр не может быть удален, т.к. связан с ПЛ');
        }
    }

    protected function disableBanIfNeed(Form $form)
    {
        $driver = Driver::where('hash_id', $form->driver_id)->first();

        if (!$driver) {
            return;
        }

        if (!$driver->end_of_ban) {
            return;
        }

        $last = Form::query()
            ->select([
                'id'
            ])
            ->where('type_anketa', FormTypeEnum::MEDIC)
            ->where('driver_id', $form->driver_id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($last->id === $form->id) {
            $driver->end_of_ban = null;
            $driver->save();
        }
    }
}
