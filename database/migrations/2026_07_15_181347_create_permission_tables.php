<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Formerly created Spatie permission tables. No-op after ADR-010 (UserRole + Policies).
 * Tables are dropped by 2026_07_22_181211_drop_spatie_permission_tables when present.
 */
return new class extends Migration
{
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
