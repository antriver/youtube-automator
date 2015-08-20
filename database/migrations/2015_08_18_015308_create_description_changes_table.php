<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDescriptionChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('description_changes', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('video_id');
            $table->longText('description');

            $table->timestamps();
            $table->dateTime('execute_at')->nullable();
            $table->integer('execute_mins_after_publish')->nullable();
            $table->dateTime('executed_at')->nullable();
            $table->boolean('success');

            $table->index('executed_at');

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('description_changes');
    }
}
