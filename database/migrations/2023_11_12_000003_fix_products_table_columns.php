<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if stock_quantity column exists and rename it to stock
        if (Schema::hasColumn('products', 'stock_quantity')) {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('stock_quantity', 'stock');
            });
        }
        
        // If stock column doesn't exist, add it
        if (!Schema::hasColumn('products', 'stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('stock')->default(0)->after('price');
            });
        }
    }

    public function down()
    {
        // Revert the changes if needed
        if (Schema::hasColumn('products', 'stock')) {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('stock', 'stock_quantity');
            });
        }
    }
};
