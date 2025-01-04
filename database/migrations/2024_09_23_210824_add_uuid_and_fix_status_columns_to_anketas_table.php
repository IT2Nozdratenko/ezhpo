<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUuidAndFixStatusColumnsToAnketasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('anketas', function (Blueprint $table) {
            $table->string('uuid', 36)->nullable();
            $table->integer('fix_status')->default(0);
            $table->boolean('transfer_status')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('anketas', function (Blueprint $table) {
            $table->dropColumn([
                'uuid',
                'fix_status',
                'transfer_status'
            ]);
        });
    }
}
