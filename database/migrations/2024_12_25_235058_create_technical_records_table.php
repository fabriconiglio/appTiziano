<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('technical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('stylist_id')->constrained('users');
            $table->datetime('service_date');
            $table->string('hair_type')->nullable();
            $table->string('scalp_condition')->nullable();
            $table->string('current_hair_color')->nullable();
            $table->string('desired_hair_color')->nullable();
            $table->text('hair_treatments')->nullable();
            $table->json('products_used')->nullable();
            $table->text('observations')->nullable();
            $table->json('photos')->nullable();
            $table->text('next_appointment_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('technical_records');
    }
};
