<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSourcesRequestsRatesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url', 300);
            $table->string('method', 50);
            $table->boolean('active');
            $table->timestamps();
        });

        Schema::create('requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('source_id', false)->unsigned()->nullable()->index();
            $table->json('source_raw');
            $table->timestamps();

            $table->foreign('source_id')->references('id')->on('sources')
                ->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::create('rates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_id', false)->unsigned()->nullable()->index();
            $table->integer('source_id', false)->unsigned()->nullable()->index();
            $table->string('currency', 10);
            $table->float('rate', 11, 4);
            $table->timestamps();

            $table->foreign('request_id')->references('id')->on('requests')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->foreign('source_id')->references('id')->on('sources')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rates');
        Schema::drop('requests');
        Schema::drop('sources');
    }
}
