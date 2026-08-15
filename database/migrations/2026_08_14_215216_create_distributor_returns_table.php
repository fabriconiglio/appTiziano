<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Devoluciones de mercadería de la distribuidora (nota de crédito interna).
 * No es un comprobante fiscal: no pasa por AFIP.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distributor_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique(); // DEV-0001

            // El cliente es de uno de los dos tipos, nunca de los dos.
            $table->foreignId('distributor_client_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('distributor_cliente_no_frecuente_id')->nullable();

            // Compra de origen. Toda devolución sale de una compra concreta.
            $table->unsignedBigInteger('distributor_technical_record_id')->nullable();
            $table->enum('origen', ['technical_record', 'cliente_no_frecuente']);

            $table->date('return_date');
            // [{product_id, nombre, cantidad, precio_unitario, subtotal}]
            $table->json('products_returned');
            $table->decimal('total_amount', 10, 2)->default(0);

            // A dónde va la plata. "vale" abre cuenta corriente con saldo a favor.
            $table->enum('destino', ['cuenta_corriente', 'efectivo', 'vale']);

            $table->text('motivo')->nullable();

            // Control del plazo de 15 días: no bloquea, pero queda registrado
            // quién autorizó la devolución fuera de término.
            $table->unsignedSmallInteger('dias_desde_compra')->default(0);
            $table->boolean('fuera_de_plazo')->default(false);
            $table->foreignId('autorizado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Las anuladas no se borran: quedan con su rastro.
            $table->timestamp('anulada_en')->nullable();
            $table->foreignId('anulada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motivo_anulacion')->nullable();

            $table->text('observations')->nullable();
            $table->timestamps();

            $table->foreign('distributor_cliente_no_frecuente_id', 'fk_returns_no_frecuente')
                ->references('id')->on('distributor_cliente_no_frecuentes')->nullOnDelete();
            $table->foreign('distributor_technical_record_id', 'fk_returns_tech_record')
                ->references('id')->on('distributor_technical_records')->nullOnDelete();

            // Una compra admite una sola devolución vigente. MySQL no tiene índice
            // único parcial (habría que excluir las anuladas), así que la regla se
            // valida en el controller y el índice es sólo para buscar rápido.
            $table->index(['distributor_technical_record_id', 'anulada_en'], 'idx_returns_compra');
            $table->index('return_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distributor_returns');
    }
};
