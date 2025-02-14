<?php

use App\FieldPrompt;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;
use Src\Terminals\Factories\SettingsFactory;
use Src\Terminals\ValueObjects\Settings;

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
            $table->unsignedBigInteger('terminal_id')->nullable();
            $table->jsonb('settings');
            $table->boolean('is_synced')->default(false);
            $table->timestamps();

            $table->foreign('terminal_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        DB::table('terminal_settings')->insert([
            'id' => Uuid::NIL,
            'terminal_id' => null,
            'settings' => json_encode(
                (new Settings(
                    SettingsFactory::makeMain(),
                    SettingsFactory::makeSystem())
                )->toArray()
            ),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
