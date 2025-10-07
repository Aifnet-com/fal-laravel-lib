<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::createIfNotExists('fal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('endpoint_id')->index();
            $table->unsignedBigInteger('data_id')->index();
            $table->unsignedBigInteger('error_id')->nullable()->index();
            $table->unsignedSmallInteger('type')->index()->default(0);
            $table->unsignedTinyInteger('status')->index();
            $table->timestamp('completed_at')->nullable()->index();
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
        Schema::dropIfExists('fal_requests');
    }
};
