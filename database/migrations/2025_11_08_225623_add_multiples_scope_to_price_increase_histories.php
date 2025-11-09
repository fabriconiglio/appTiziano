<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el enum para incluir 'multiples'
        DB::statement("ALTER TABLE price_increase_histories MODIFY COLUMN scope_type ENUM('producto', 'marca', 'multiples') COMMENT 'Tipo de alcance: producto individual, marca o varios productos'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir al enum original
        DB::statement("ALTER TABLE price_increase_histories MODIFY COLUMN scope_type ENUM('producto', 'marca') COMMENT 'Tipo de alcance: producto individual o marca'");
    }
};
