<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 1000)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('route', 1000)->nullable()->comment('Comma separated IDs.');
            $table->string('slug', 1000)->nullable()->comment('A slug for the route URL.');
            $table->string('best_route', 1000)->nullable()->comment('Comma separated IDs.');
            $table->string('custom_start_coordinates', 1000)->nullable()->comment('Latitude and longitude, separated with comma.');
            $table->boolean('public')->default(true);
            $table->softDeletes('deleted_at');
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
        Schema::dropIfExists('routes');
    }
}
