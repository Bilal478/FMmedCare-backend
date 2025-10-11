<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    use HasFactory;

    protected $table = 'audit_trail';

    // Since this is a view, we set it as non-incrementing
    public $incrementing = false;

    // Make all attributes mass assignable since it's a read-only view
    protected $guarded = [];

    // Disable timestamps for the view
    public $timestamps = false;

    // Casts for the view
    protected $casts = [
        'dob' => 'date',
        'date_of_service' => 'date',
        'dates_paid' => 'date',
        'date_claim_submitted' => 'date',
        'billed_amount' => 'decimal:2',
        'insurance_paid' => 'decimal:2',
        'patient_responsibility' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'auth_required_yn' => 'boolean',
        'statement_sent' => 'boolean',
        'payment_plan_yn' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}