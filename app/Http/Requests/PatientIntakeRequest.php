<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientIntakeRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            // Enrollment ID is auto-generated, so not required in request
            'patient_info.patient_name' => 'required|string|max:255',
            'patient_info.dob' => 'required|date',
            'patient_info.gender' => 'required|in:Male,Female,Other',
            'patient_info.member_id' => 'required|string|max:100',
            'patient_info.phone' => 'required|string|max:20',
            'patient_info.address' => 'required|string',
            'patient_info.date_of_service' => 'required|date',
            
            'selection_info.insurance' => 'required|in:Medicare,Medicaid,Insurance,Self-Pay',
            'selection_info.vendor' => 'required|string|max:255',
            
            'physician_info.primary_physician' => 'nullable|string|max:255',
            'physician_info.physician_npi' => 'nullable|string|max:20',
            'physician_info.prescribing_provider' => 'nullable|string|max:255',
            
            'clinical_info.diagnosis_icd10' => 'nullable|string|max:20',
            'clinical_info.date_of_prescription' => 'nullable|date',
            'clinical_info.dme_items' => 'required|string|max:255',
            'clinical_info.number_of_items' => 'required|integer|min:1',
            'clinical_info.hcpcs_codes' => 'required|array|min:1',
            'clinical_info.hcpcs_codes.*' => 'string|max:10',
            'clinical_info.medical_necessity_yn' => 'boolean',
            'clinical_info.prior_auth_yn' => 'boolean',
            'clinical_info.auth_number' => 'nullable|string|max:100',
            
            'delivery_tracking.date_of_shipment' => 'nullable|date',
            'delivery_tracking.estimated_delivery_date' => 'nullable|date',
            'delivery_tracking.carrier_service' => 'nullable|in:FedEx,USPS,DHL',
            'delivery_tracking.tracking_number' => 'nullable|string|max:100',
            'delivery_tracking.proof_of_delivery' => 'nullable|string',
            'delivery_tracking.additional_notes' => 'nullable|string',
        ];

        // For update, don't require all fields
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = array_map(function ($rule) {
                if (is_string($rule) && str_contains($rule, 'required')) {
                    return str_replace('required', 'sometimes', $rule);
                }
                return $rule;
            }, $rules);
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'patient_info.patient_name.required' => 'Patient name is required',
            'patient_info.dob.required' => 'Date of birth is required',
            'patient_info.gender.required' => 'Gender is required',
            'patient_info.member_id.required' => 'Member ID is required',
            'patient_info.phone.required' => 'Phone number is required',
            'patient_info.address.required' => 'Address is required',
            'patient_info.date_of_service.required' => 'Date of service is required',
            'selection_info.insurance.required' => 'Insurance type is required',
            'selection_info.vendor.required' => 'Vendor is required',
            'clinical_info.dme_items.required' => 'DME item is required',
            'clinical_info.number_of_items.required' => 'Number of items is required',
            'clinical_info.hcpcs_codes.required' => 'At least one HCPCS code is required',
        ];
    }
}