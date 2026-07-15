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
        Schema::table('ebook_transactions', function (Blueprint $table) {
            $table->boolean('confirmation_requested')->default(false)->after('admin_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ebook_transactions', function (Blueprint $table) {
            $table->dropColumn('confirmation_requested');
        });
    }
};
