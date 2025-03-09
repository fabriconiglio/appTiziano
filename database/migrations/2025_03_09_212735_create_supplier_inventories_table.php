<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('sku')->nullable()->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity')->default(0);
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->string('supplier_name');
            $table->string('supplier_contact')->nullable();
            $table->string('supplier_email')->nullable();
            $table->string('supplier_phone')->nullable();
            $table->date('last_restock_date')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->string('status')->default('available'); // available, low_stock, out_of_stock
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_inventories');
    }
}
