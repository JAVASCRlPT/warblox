<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the new application role value before updating legacy data.
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('student','librarian','admin','mahasiswa') NOT NULL DEFAULT 'student'");

        // Normalize existing legacy role values
        DB::table('users')->where('role', 'student')->update(['role' => 'mahasiswa']);
        DB::table('users')->where('role', 'librarian')->update(['role' => 'admin']);

        // Clean up the enum values to only current application roles.
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','mahasiswa') NOT NULL DEFAULT 'mahasiswa'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('student','librarian','admin') NOT NULL DEFAULT 'student'");
    }
};
