<?php

use App\FieldPrompt;
use Illuminate\Database\Migrations\Migration;

class ChangeOfficialTypeAutoColumnTitle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        FieldPrompt::query()
            ->where('type', 'car')
            ->where('field', 'official_type_auto')
            ->update(
                [
                    'name' => 'Тип Т\С',
                ]
            );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        FieldPrompt::query()
            ->where('type', 'car')
            ->where('field', 'official_type_auto')
            ->update(
                [
                    'name' => 'Категория Т\С (из документов)',
                ]
            );
    }
}
