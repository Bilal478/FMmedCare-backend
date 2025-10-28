<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('billing_payments', function (Blueprint $table) {
            // Change payer from enum to string
            $table->string('payer')->change();
        });
    }

    public function down()
    {
        Schema::table('billing_payments', function (Blueprint $table) {
            // Revert back to enum if needed
            $table->enum('payer', ['Medicare', 'Medicaid', 'Insurance', 'Self-Pay'])->change();
        });
    }
};