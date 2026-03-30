<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Los destacados del e-commerce pasan a ser solo inventario distribuidora (supplier_inventories).
     */
    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('products', 'is_featured')) {
            return;
        }

        DB::table('products')->update(['is_featured' => false]);
    }

    /**
     * No se restauran valores previos de is_featured.
     */
    public function down(): void
    {
        //
    }
};
