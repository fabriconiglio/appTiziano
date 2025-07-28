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
        Schema::create('distributor_technical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_client_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->datetime('purchase_date');
            $table->string('purchase_type')->nullable(); // Tipo de compra: al por mayor, al por menor, etc.
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('payment_method')->nullable(); // Método de pago
            $table->json('products_purchased')->nullable(); // Productos comprados con cantidades
            $table->text('observations')->nullable();
            $table->json('photos')->nullable();
            $table->text('next_purchase_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('distributor_technical_records');
    }
};
