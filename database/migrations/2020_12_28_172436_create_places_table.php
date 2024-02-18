<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id', 200);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('city_id');
            $table->foreign('city_id')->references('id')->on('localities');
            $table->unsignedBigInteger('county_id');
            $table->foreign('county_id')->references('id')->on('localities');
            $table->boolean('sticky')->default(false);
            $table->string('name', 1000)->default('Untitled');
            $table->text('description')->nullable();
            $table->string('latitude', 300);
            $table->string('longitude', 300);
            $table->string('address', 1000)->nullable();
            $table->string('website', 1000)->nullable();
            $table->string('facebook_page', 1000)->nullable();
            $table->string('tags', 1000)->comment('Comma separated values.');
            $table->string('status', 1000)->default('inactive')->comment('Active or inactive.');
            $table->boolean('accessible_with_car')->default(0);
            $table->boolean('disabled_accessible')->default(0);
            $table->boolean('kid_friendly')->default(0);
            $table->float('rating', 8, 2)->nullable();
            $table->bigInteger('row_order')->default(1);
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
        Schema::dropIfExists('places');
    }
}
