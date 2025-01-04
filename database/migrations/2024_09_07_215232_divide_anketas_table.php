<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DivideAnketasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('uuid', 36)->unique();
            $table->string('type_anketa');

            $table->unsignedBigInteger('deleted_id')->nullable();
            $table->foreign('deleted_id')->references('id')->on('users');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('user_eds')->nullable();
            $table->date('user_validity_eds_start')->nullable();
            $table->date('user_validity_eds_end')->nullable();

            $table->unsignedBigInteger('point_id')->nullable();
            $table->foreign('point_id')->references('id')->on('points');

            $table->string('driver_id', 6)->nullable();
            $table->foreign('driver_id')->references('hash_id')->on('drivers');

            $table->string('company_id')->nullable();
            $table->foreign('company_id')->references('hash_id')->on('companies');

            $table->dateTime('date')->nullable();

            $table->string('realy')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('medic_forms', function (Blueprint $table) {
            $table->string('forms_uuid', 36)->unique()->primary();
            $table->foreign('forms_uuid')->references('uuid')->on('forms');

            $table->string('type_view');

            $table->unsignedBigInteger('operator_id')->nullable();
            $table->foreign('operator_id')->references('id')->on('users');

            $table->string('driver_group_risk')->nullable();

            $table->string('admitted')->default('Не допущен');
            $table->string('complaint')->default('Нет');
            $table->string('condition_visible_sliz')->default('Без особенностей');
            $table->string('condition_koj_pokr')->default('Без особенностей');

            $table->string('pressure')->nullable();
            $table->string('tonometer')->nullable();
            $table->double('t_people')->nullable();
            $table->integer('pulse')->nullable();

            $table->integer('alcometer_mode')->default(0);
            $table->double('alcometer_result')->nullable();
            $table->string('proba_alko')->default('Отрицательно');
            $table->string('test_narko')->default('Не проводился');
            $table->string('med_view')->default('В норме');

            $table->text('photos')->nullable();
            $table->text('videos')->nullable();

            $table->text('protokol_path')->nullable();
            $table->text('closing_path')->nullable();

            $table->boolean('is_dop')->default(false);
            $table->string('period_pl')->nullable();
            $table->string('result_dop')->nullable();

            $table->unsignedBigInteger('terminal_id')->nullable();
            $table->foreign('terminal_id')->references('id')->on('users');

            $table->string('flag_pak')->nullable();
        });

        Schema::create('tech_forms', function (Blueprint $table) {
            $table->string('forms_uuid', 36)->unique()->primary();
            $table->foreign('forms_uuid')->references('uuid')->on('forms');

            $table->string('type_view');

            $table->string('car_id', 6)->nullable();
            $table->foreign('car_id')->references('hash_id')->on('cars');
            $table->string('car_gos_number')->nullable();

            $table->string('number_list_road')->nullable();
            $table->integer('odometer')->nullable();
            $table->string('point_reys_control')->nullable();

            $table->boolean('is_dop')->default(false);
            $table->string('period_pl')->nullable();
            $table->string('result_dop')->nullable();
        });

        Schema::create('bdd_forms', function (Blueprint $table) {
            $table->string('forms_uuid', 36)->unique()->primary();
            $table->foreign('forms_uuid')->references('uuid')->on('forms');

            $table->string('type_briefing');
            $table->string('briefing_name')->nullable();
            $table->string('signature')->nullable();
        });

        Schema::create('print_pl_forms', function (Blueprint $table) {
            $table->string('forms_uuid', 36)->unique()->primary();
            $table->foreign('forms_uuid')->references('uuid')->on('forms');

            $table->integer('count_pl');
        });

        Schema::create('report_cart_forms', function (Blueprint $table) {
            $table->string('forms_uuid', 36)->unique()->primary();
            $table->foreign('forms_uuid')->references('uuid')->on('forms');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medic_forms');
        Schema::dropIfExists('tech_forms');
        Schema::dropIfExists('bdd_forms');
        Schema::dropIfExists('print_pl_forms');
        Schema::dropIfExists('report_cart_forms');
        Schema::dropIfExists('forms');
    }
}
