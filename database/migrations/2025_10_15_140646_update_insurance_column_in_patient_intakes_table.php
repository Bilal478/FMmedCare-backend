<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_intakes', function (Blueprint $table) {
            $table->string('insurance')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_intakes', function (Blueprint $table) {
            $table->enum('insurance', ['Medicare', 'Medicaid', 'Insurance', 'Self-Pay'])->change();
        });
    }
};
