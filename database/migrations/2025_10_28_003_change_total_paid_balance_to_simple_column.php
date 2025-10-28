<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Step 1: Create a temporary column
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->decimal('total_paid_balance_temp', 10, 2)->default(0.00)->after('total_paid_balance');
        });

        // Step 2: Copy data from old column to new column
        DB::statement('UPDATE billing_payments SET total_paid_balance_temp = total_paid_balance');

        // Step 3: Drop the old generated column
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->dropColumn('total_paid_balance');
        });

        // Step 4: Rename the temporary column to the original name
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->renameColumn('total_paid_balance_temp', 'total_paid_balance');
        });
    }

    public function down()
    {
        // For rollback, create a temporary column
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->decimal('total_paid_balance_temp', 10, 2)->storedAs('insurance_paid')->after('total_paid_balance');
        });

        // Copy data
        DB::statement('UPDATE billing_payments SET total_paid_balance_temp = total_paid_balance');

        // Drop current column
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->dropColumn('total_paid_balance');
        });

        // Rename back
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->renameColumn('total_paid_balance_temp', 'total_paid_balance');
        });
    }
};