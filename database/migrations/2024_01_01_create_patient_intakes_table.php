<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('patient_intakes', function (Blueprint $table) {
            $table->id();
            
            // Enrollment Information
            $table->string('enrollment_id', 20)->unique();
            
            // Patient Information
            $table->string('patient_name');
            $table->date('dob');
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->string('member_id', 100);
            $table->string('phone', 20);
            $table->text('address');
            $table->date('date_of_service');
            
            // Selection Information
            $table->enum('insurance', ['Medicare', 'Medicaid', 'Insurance', 'Self-Pay']);
            $table->string('vendor');
            
            // Physician Information
            $table->string('primary_physician')->nullable();
            $table->string('physician_npi', 20)->nullable();
            $table->string('prescribing_provider')->nullable();
            
            // Clinical Information
            $table->string('diagnosis_icd10')->nullable();
            $table->date('date_of_prescription')->nullable();
            $table->string('dme_items');
            $table->integer('number_of_items')->default(1);
            $table->json('hcpcs_codes');
            $table->boolean('medical_necessity_yn')->default(false);
            $table->boolean('prior_auth_yn')->default(false);
            $table->string('auth_number')->nullable();
            
            // Delivery Tracking
            $table->date('date_of_shipment')->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->enum('carrier_service', ['FedEx', 'USPS', 'DHL'])->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('proof_of_delivery')->nullable();
            $table->text('additional_notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('patient_intakes');
    }
};