<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRelatedUserIdColumnToCompaniesAndDriversTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('related_user_id')->nullable()->after('hash_id');
            $table->foreign('related_user_id')
                ->references('id')
                ->on('users');
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->unsignedBigInteger('related_user_id')->nullable()->after('hash_id');
            $table->foreign('related_user_id')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['related_user_id']);
            $table->dropColumn('related_user_id');
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropForeign(['related_user_id']);
            $table->dropColumn('related_user_id');
        });
    }
}
