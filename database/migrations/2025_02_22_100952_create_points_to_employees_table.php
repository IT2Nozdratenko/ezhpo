<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointsToEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('points_to_employees', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');

            $table->unsignedBigInteger('point_id');
            $table->foreign('point_id')
                ->references('id')
                ->on('points')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('points_to_employees');
    }
}
