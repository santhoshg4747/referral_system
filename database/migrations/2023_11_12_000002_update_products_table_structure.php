<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add missing columns if they don't exist
        if (!Schema::hasColumn('products', 'description')) {
            Schema::table('products', function (Blueprint $table) {
                $table->text('description')->nullable()->after('title');
            });
        }

        if (!Schema::hasColumn('products', 'is_active')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('image_url');
            });
        }

        // Make sure price is decimal(10,2)
        if (Schema::hasColumn('products', 'price')) {
            // For MySQL
            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE products MODIFY price DECIMAL(10,2) NOT NULL');
            }
        }
    }

    public function down()
    {
        // This migration only adds columns, so the down method can be left empty
        // as we don't want to drop columns that might contain data
    }
};
