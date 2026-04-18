<?php

use App\Models\SupplierInventory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->string('slug', 191)->nullable()->unique()->after('product_name');
        });

        SupplierInventory::query()->orderBy('id')->each(function (SupplierInventory $item) {
            $base = Str::slug($item->product_name) ?: 'producto';
            $slug = $base.'-'.$item->id;
            $candidate = $slug;
            $n = 2;
            while (SupplierInventory::where('slug', $candidate)->where('id', '!=', $item->id)->exists()) {
                $candidate = $slug.'-'.$n++;
            }
            $item->slug = $candidate;
            $item->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_inventories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
