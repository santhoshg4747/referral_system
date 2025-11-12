<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add missing columns if they don't exist
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('products', 'stock')) {
                $table->integer('stock')->default(0)->after('price');
            }
            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('image_url');
            }
            
            // Make sure price is decimal(10,2)
            $table->decimal('price', 10, 2)->change();
        });
    }

    public function down()
    {
        // This migration only adds columns, so the down method can be left empty
        // as we don't want to drop columns that might contain data
    }
};
