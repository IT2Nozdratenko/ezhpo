<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('related_user_id');
            $table->foreign('related_user_id')
                ->references('id')
                ->on('users');

            $table->string('hash_id')->default('000000');
            $table->string('name');
            $table->integer('blocked')->default(0);
            $table->integer('pv_id')->default(0);
            $table->string('eds', 191)->nullable();
            $table->date('validity_eds_start')->nullable();
            $table->date('validity_eds_end')->nullable();
            $table->boolean('auto_created')->default(false);
            $table->softDeletes();
            $table->string('deleted_id', 191)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
