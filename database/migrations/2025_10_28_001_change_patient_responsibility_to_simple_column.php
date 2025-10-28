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
            $table->decimal('patient_responsibility_temp', 10, 2)->default(0.00)->after('patient_responsibility');
        });

        // Step 2: Copy data from old column to new column
        DB::statement('UPDATE billing_payments SET patient_responsibility_temp = patient_responsibility');

        // Step 3: Drop the old generated column
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->dropColumn('patient_responsibility');
        });

        // Step 4: Rename the temporary column to the original name
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->renameColumn('patient_responsibility_temp', 'patient_responsibility');
        });
    }

    public function down()
    {
        // For rollback, we'll just keep it as a simple decimal since we can't easily revert to storedAs
        Schema::table('billing_payments', function (Blueprint $table) {
            $table->decimal('patient_responsibility', 10, 2)->default(0.00)->change();
        });
    }
};