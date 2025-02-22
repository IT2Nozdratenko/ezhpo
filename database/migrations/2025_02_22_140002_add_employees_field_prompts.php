<?php

use App\FieldPrompt;
use Illuminate\Database\Migrations\Migration;

class AddEmployeesFieldPrompts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $items = FieldPrompt::query()
            ->where('type', '=', 'users')
            ->get()
            ->toArray();

        foreach ($items as $item) {
            $item['type'] = 'employees';

            FieldPrompt::create($item);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        FieldPrompt::query()
            ->where('type', '=', 'employees')
            ->forceDelete();
    }
}
