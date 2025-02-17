<?php

use App\FieldPrompt;
use App\Models\TripTicket;
use Illuminate\Database\Migrations\Migration;

class InsertTripTicketsPhotosFieldToFieldPromptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $period = [
            'attributes' => [
                'type' => TripTicket::SLUG,
                'field' => 'photos'
            ],
            'values' => [
                'name' => 'Фото',
                'sort' => FieldPrompt::where('type', '=', TripTicket::SLUG)->orderByDesc('sort')->first()->sort + 1,
            ]
        ];

        FieldPrompt::query()->updateOrCreate($period['attributes'], $period['values']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('field_prompts')
            ->where([
                'type' => TripTicket::SLUG,
                'field' => 'photos',
            ])
            ->delete();
    }
}
