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
            $table->string('payment_method')->nullable()->after('checkout_id');
            $table->unsignedInteger('admin_fee')->default(0)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ebook_transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'admin_fee']);
        });
    }
};
