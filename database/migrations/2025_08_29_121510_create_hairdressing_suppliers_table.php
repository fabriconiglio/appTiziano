<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hairdressing_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('cuit')->nullable();
            $table->string('business_name')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('delivery_time')->nullable();
            $table->decimal('minimum_order', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->string('website')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('tax_category')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hairdressing_suppliers');
    }
};
