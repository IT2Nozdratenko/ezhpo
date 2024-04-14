<?php

use App\FieldPrompt;
use Illuminate\Database\Migrations\Migration;

class SortTerminalsFieldPrompts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $fields = [
            'status',
            'hash_id',
            'name',
            'failures_count',
            'date_check',
            'serial_number',
            'company_id',
            'stamp_id',
            'town',
            'pv',
            'timezone',
            'blocked',
            'api_token',
            'last_month_amount',
            'month_amount',
            'date_service_start',
            'date_service_end',
        ];

        foreach ($fields as $sort => $field) {
            FieldPrompt::query()
                ->where('type', 'terminals')
                ->where('field', $field)
                ->update(['sort' => $sort]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
