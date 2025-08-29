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
        Schema::create('distributor_current_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_client_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('distributor_technical_record_id')->nullable();
            $table->enum('type', ['debt', 'payment']); // Tipo de movimiento: deuda o pago
            $table->decimal('amount', 10, 2); // Monto del movimiento
            $table->text('description'); // Descripción del movimiento
            $table->date('date'); // Fecha del movimiento
            $table->string('reference')->nullable(); // Referencia (número de factura, recibo, etc.)
            $table->text('observations')->nullable(); // Observaciones adicionales
            $table->timestamps();

            // Agregar la clave foránea con un nombre más corto
            $table->foreign('distributor_technical_record_id', 'fk_current_accounts_tech_record')
                  ->references('id')
                  ->on('distributor_technical_records')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('distributor_current_accounts');
    }
};
