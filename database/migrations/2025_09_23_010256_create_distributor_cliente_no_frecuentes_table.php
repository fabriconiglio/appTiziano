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
        Schema::create('distributor_cliente_no_frecuentes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable(); // Nombre del cliente (opcional)
            $table->string('telefono')->nullable(); // Teléfono del cliente (opcional)
            $table->date('fecha'); // Fecha de la venta
            $table->decimal('monto', 10, 2); // Monto de la venta
            $table->string('distribuidor'); // Nombre del distribuidor
            $table->text('productos')->nullable(); // Productos vendidos (opcional)
            $table->text('observaciones')->nullable(); // Observaciones adicionales
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Usuario que registró
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributor_cliente_no_frecuentes');
    }
};
