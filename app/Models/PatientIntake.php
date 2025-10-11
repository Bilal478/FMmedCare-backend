<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientIntake extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id', 'patient_name', 'dob', 'gender', 'member_id', 
        'phone', 'address', 'date_of_service', 'insurance', 'vendor',
        'primary_physician', 'physician_npi', 'prescribing_provider',
        'diagnosis_icd10', 'date_of_prescription', 'dme_items', 
        'number_of_items', 'hcpcs_codes', 'medical_necessity_yn', 
        'prior_auth_yn', 'auth_number', 'date_of_shipment', 
        'estimated_delivery_date', 'carrier_service', 'tracking_number',
        'proof_of_delivery', 'additional_notes'
    ];

    protected $casts = [
        'hcpcs_codes' => 'array',
        'medical_necessity_yn' => 'boolean',
        'prior_auth_yn' => 'boolean',
        'dob' => 'date',
        'date_of_service' => 'date',
        'date_of_prescription' => 'date',
        'date_of_shipment' => 'date',
        'estimated_delivery_date' => 'date'
    ];

    // Auto-generate enrollment ID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->enrollment_id)) {
                $latest = PatientIntake::orderBy('id', 'desc')->first();
                $nextId = $latest ? intval(substr($latest->enrollment_id, 2)) + 1 : 1;
                $model->enrollment_id = 'FM' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function billingPayments()
    {
        return $this->hasMany(BillingPayment::class);
    }
}