<?php

use App\FieldPrompt;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTerminalSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('terminal_settings', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->bigInteger('terminal_id');
            $table->jsonb('settings')->default(json_encode([]));
            $table->boolean('is_synced')->default(false);
            $table->timestamps();

            $table->foreign('terminal_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('terminal_settings');
    }
}
