<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTerminalIdColumnToTerminalChecksAndDevicesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('terminal_checks', function (Blueprint $table) {
            $table->unsignedBigInteger('terminal_id')->nullable()->after('user_id');
            $table->foreign('terminal_id')->references('id')->on('terminals')->onDelete('cascade');
        });

        Schema::table('terminal_devices', function (Blueprint $table) {
            $table->unsignedBigInteger('terminal_id')->nullable()->after('user_id');
            $table->foreign('terminal_id')->references('id')->on('terminals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('terminal_checks', function (Blueprint $table) {
            $table->dropForeign(['terminal_id']);
            $table->dropColumn('terminal_id');
        });

        Schema::table('terminal_devices', function (Blueprint $table) {
            $table->dropForeign(['terminal_id']);
            $table->dropColumn('terminal_id');
        });
    }
}
