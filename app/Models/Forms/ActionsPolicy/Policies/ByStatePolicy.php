<?php

namespace App\Models\Forms\ActionsPolicy\Policies;

use App\Enums\FlagPakEnum;
use App\Models\Forms\Form;
use App\Models\TripTicket;
use App\User;
use App\Models\Forms\ActionsPolicy\Contracts\PolicyInterface;

class ByStatePolicy implements PolicyInterface
{
    /**
     * @var Form
     */
    private $form;

    /**
     * @var User|null
     */
    private $user;

    /**
     * @var array
     */
    private $disabledAttributesMap;

    /**
     * @var array
     */
    private $hiddenAttributesMap;

    public function __construct(Form $form, ?User $user)
    {
        $this->form = $form;
        $this->user = $user;

        $this->flush();
    }

    private function flush()
    {
        $this->flushDisabledAttributes();
        $this->flushHiddenAttributes();
    }

    public function getDisabledAttributesMap(): array
    {
        return array_keys($this->disabledAttributesMap);
    }

    public function getHiddenAttributesMap(): array
    {
        return array_keys($this->hiddenAttributesMap);
    }

    public function isAttributeDisabled(string $attribute): bool
    {
        return isset($this->disabledAttributesMap[$attribute]);
    }

    public function isAttributeHidden(string $attribute): bool
    {
        return isset($this->hiddenAttributesMap[$attribute]);
    }

    private function flushDisabledAttributes()
    {
        $this->disabledAttributesMap = [];

        $this->disablePermanently();
        $this->disableIfFilled();
    }

    private function disablePermanently()
    {
        $details = $this->form->details;

        $permanentlyDisabledConditions = [];

        /** Относится к ПЛ */
        $permanentlyDisabledConditions[] = TripTicket::query()
            ->where('medic_form_id', $this->form->id)
            ->orWhere('tech_form_id', $this->form->id)
            ->exists();

        /** Относится к СДПО */
        $permanentlyDisabledConditions[] = in_array(
            $details->getAttribute('flag_pak'),
            [FlagPakEnum::SDPO_R, FlagPakEnum::SDPO_A]
        );

        $permanentlyDisabled = in_array(true, $permanentlyDisabledConditions);
        if (!$permanentlyDisabled) {
            return;
        }

        foreach (array_merge($this->form->fillable, $details->fillable) as $attribute) {
            $this->disabledAttributesMap[$attribute] = true;
        }
    }

    private function disableIfFilled()
    {
        $disabledIfFilled = [
            'driver_id',
            'company_id',
            'car_id',
            'type_view',
            'period_pl',
            'car_type_auto'
        ];

        $details = $this->form->details;

        /** Не "Ввод ПЛ" или "Утвержденный" ПЛ */
        if (!$details->is_dop || ($details->result_dop !== null)) {
            $disabledIfFilled[] = 'point_id';
            $disabledIfFilled[] = 'date';
        }

        foreach ($disabledIfFilled as $attribute) {
            if ($this->form->getAttribute($attribute) !== null) {
                $this->disabledAttributesMap[$attribute] = true;

                continue;
            }

            if ($details->getAttribute($attribute) !== null) {
                $this->disabledAttributesMap[$attribute] = true;
            }
        }
    }

    private function flushHiddenAttributes()
    {
        $this->hiddenAttributesMap = [];
    }
}
