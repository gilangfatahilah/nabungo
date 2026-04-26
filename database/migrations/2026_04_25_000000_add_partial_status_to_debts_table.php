<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Adds 'partial' as a valid status value for the debts table.
     * Laravel's enum on PostgreSQL is a varchar with a CHECK constraint.
     */
    public function up(): void
    {
        // Drop the existing check constraint (Laravel names it <table>_<column>_check)
        DB::statement("ALTER TABLE debts DROP CONSTRAINT IF EXISTS debts_status_check");

        // Re-add the check constraint with 'partial' included
        DB::statement("ALTER TABLE debts ADD CONSTRAINT debts_status_check CHECK (status IN ('unpaid', 'partial', 'paid'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE debts DROP CONSTRAINT IF EXISTS debts_status_check");
        DB::statement("UPDATE debts SET status = 'unpaid' WHERE status = 'partial'");
        DB::statement("ALTER TABLE debts ADD CONSTRAINT debts_status_check CHECK (status IN ('unpaid', 'paid'))");
    }
};
