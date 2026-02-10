<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make users.phone nullable while keeping unique constraint.
     * This allows:
     * - Multiple NULL phones (social login)
     * - Unique non-NULL phones (manual signup)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ensure phone column exists before modifying
            if (Schema::hasColumn('users', 'phone')) {
                // Make phone nullable (no data loss)
                $table->string('phone', 50)->nullable()->change();
            }
        });
    }

    /**
     * Rollback: restore NOT NULL (original behavior)
     * Kept for completeness, but normally not needed.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 50)->nullable(false)->change();
            }
        });
    }
};
