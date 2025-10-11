<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('billing_payments', function (Blueprint $table) {
            $table->id();
            
            // References to Patient Intake (Non-editable fields)
            $table->string('patient_name');
            $table->string('enrollment_id', 20);
            $table->string('member_id', 100);
            $table->string('dme_item');
            $table->string('hcpcs');
            $table->enum('payer', ['Medicare', 'Medicaid', 'Insurance', 'Self-Pay']);
            
            // Billing Information (Editable fields)
            $table->decimal('total_claim_amount', 10, 2)->default(0.00);
            $table->decimal('allowed_amount', 10, 2)->default(0.00);
            $table->decimal('insurance_paid', 10, 2)->default(0.00);
            $table->date('date_paid')->nullable();
            $table->enum('is_paid', ['Yes', 'No', 'Partial'])->default('No');
            $table->decimal('patient_responsibility', 10, 2)->storedAs('total_claim_amount - allowed_amount');
            $table->decimal('total_paid_balance', 10, 2)->storedAs('insurance_paid');
            $table->text('notes');
            
            // Optional Fields
            $table->boolean('authorization_yn')->default(false);
            $table->enum('billing_status', ['Submitted', 'Pending', 'Denied', 'Paid', 'In Process'])->default('Pending');
            $table->date('date_of_service')->nullable();
            $table->date('date_claim_submission')->nullable();
            $table->string('claim_number')->nullable();
            
            // Foreign Key
            $table->foreignId('patient_intake_id')->nullable()->constrained('patient_intakes')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('enrollment_id');
            $table->index('billing_status');
            $table->index('date_paid');
        });
    }

    public function down()
    {
        Schema::dropIfExists('billing_payments');
    }
};