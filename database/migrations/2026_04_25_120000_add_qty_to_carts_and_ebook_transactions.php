<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('carts') && ! Schema::hasColumn('carts', 'qty')) {
            Schema::table('carts', function (Blueprint $table): void {
                $table->unsignedInteger('qty')->default(1)->after('book_id');
            });
        }

        if (Schema::hasTable('ebook_transactions') && ! Schema::hasColumn('ebook_transactions', 'qty')) {
            Schema::table('ebook_transactions', function (Blueprint $table): void {
                $table->unsignedInteger('qty')->default(1)->after('amount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('carts') && Schema::hasColumn('carts', 'qty')) {
            Schema::table('carts', function (Blueprint $table): void {
                $table->dropColumn('qty');
            });
        }

        if (Schema::hasTable('ebook_transactions') && Schema::hasColumn('ebook_transactions', 'qty')) {
            Schema::table('ebook_transactions', function (Blueprint $table): void {
                $table->dropColumn('qty');
            });
        }
    }
};
