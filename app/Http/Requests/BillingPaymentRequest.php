<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BillingPaymentRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            'patient_name' => 'required|string|max:255',
            'enrollment_id' => 'required|string|exists:patient_intakes,enrollment_id',
            'member_id' => 'required|string|max:100',
            'dme_item' => 'required|string|max:255',
            'hcpcs' => 'required|string|max:255',
            'payer' => 'required|in:Medicare,Medicaid,Insurance,Self-Pay',
            'total_claim_amount' => 'required|numeric|min:0',
            'allowed_amount' => 'required|numeric|min:0',
            'insurance_paid' => 'required|numeric|min:0',
            'date_paid' => 'required|date',
            'is_paid' => 'required|in:Yes,No,Partial',
            'notes' => 'required|string',
            'authorization_yn' => 'boolean',
            'billing_status' => 'required|in:Submitted,Pending,Denied,Paid,In Process',
            'date_of_service' => 'nullable|date',
            'date_claim_submission' => 'nullable|date',
            'claim_number' => 'nullable|string|max:100',
            'patient_intake_id' => 'nullable|exists:patient_intakes,id'
        ];

        // For update, make fields sometimes required
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            foreach ($rules as $field => $rule) {
                if (str_contains($rule, 'required')) {
                    $rules[$field] = str_replace('required', 'sometimes', $rule);
                }
            }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'enrollment_id.exists' => 'The selected enrollment ID does not exist in patient records',
            'patient_intake_id.exists' => 'The selected patient intake record does not exist',
            'total_claim_amount.required' => 'Total claim amount is required',
            'allowed_amount.required' => 'Allowed amount is required',
            'insurance_paid.required' => 'Insurance paid amount is required',
            'date_paid.required' => 'Date paid is required',
            'is_paid.required' => 'Payment status is required',
            'notes.required' => 'Notes are required',
            'billing_status.required' => 'Billing status is required',
        ];
    }
}