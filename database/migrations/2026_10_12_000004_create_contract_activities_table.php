<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractActivitiesTable extends Migration
{
    public function up()
    {
        Schema::create('contract_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('action'); // created, updated, approved, rejected, deleted
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->index('contract_id');
            $table->index('action');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contract_activities');
    }
}
