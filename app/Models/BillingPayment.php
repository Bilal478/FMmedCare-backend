<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_name', 'enrollment_id', 'member_id', 'dme_item', 
        'hcpcs', 'payer', 'total_claim_amount', 'allowed_amount', 
        'insurance_paid', 'date_paid', 'is_paid', 'notes',
        'authorization_yn', 'billing_status', 'date_of_service',
        'date_claim_submission', 'claim_number', 'patient_intake_id'
    ];

    protected $casts = [
        'total_claim_amount' => 'decimal:2',
        'allowed_amount' => 'decimal:2',
        'insurance_paid' => 'decimal:2',
        'patient_responsibility' => 'decimal:2',
        'total_paid_balance' => 'decimal:2',
        'authorization_yn' => 'boolean',
        'date_paid' => 'date',
        'date_of_service' => 'date',
        'date_claim_submission' => 'date'
    ];

    public function patientIntake()
    {
        return $this->belongsTo(PatientIntake::class);
    }

    // Auto-populate authorization fields from patient intake
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->patient_intake_id) {
                $patientIntake = PatientIntake::find($model->patient_intake_id);
                if ($patientIntake) {
                    $model->authorization_yn = $patientIntake->prior_auth_yn;
                    // Copy other non-editable fields if not set
                    if (empty($model->patient_name)) {
                        $model->patient_name = $patientIntake->patient_name;
                    }
                    if (empty($model->enrollment_id)) {
                        $model->enrollment_id = $patientIntake->enrollment_id;
                    }
                    if (empty($model->member_id)) {
                        $model->member_id = $patientIntake->member_id;
                    }
                    if (empty($model->dme_item)) {
                        $model->dme_item = $patientIntake->dme_items;
                    }
                    if (empty($model->hcpcs)) {
                        $hcpcsCodes = $patientIntake->hcpcs_codes;
                        $model->hcpcs = !empty($hcpcsCodes) ? $hcpcsCodes[0] : '';
                    }
                    if (empty($model->payer)) {
                        $model->payer = $patientIntake->insurance;
                    }
                }
            }
        });
    }
}