<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientIntakeRequest;
use App\Models\PatientIntake;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientIntakeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PatientIntake::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('enrollment_id', 'like', "%{$search}%")
                  ->orWhere('member_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $perPage = $request->per_page ?? 15;
        $patientIntakes = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $patientIntakes->items(),
            'pagination' => [
                'current_page' => $patientIntakes->currentPage(),
                'per_page' => $patientIntakes->perPage(),
                'total' => $patientIntakes->total(),
                'last_page' => $patientIntakes->lastPage(),
            ],
            'message' => 'Patient intake records retrieved successfully'
        ]);
    }

    public function store(PatientIntakeRequest $request): JsonResponse
    {
        $data = $this->transformRequestData($request->all());

        $patientIntake = PatientIntake::create($data);

        return response()->json([
            'success' => true,
            'data' => $patientIntake,
            'message' => 'Patient intake record created successfully'
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $patientIntake = PatientIntake::with('billingPayments')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $patientIntake,
            'message' => 'Patient intake record retrieved successfully'
        ]);
    }

    public function update(PatientIntakeRequest $request, $id): JsonResponse
    {
        $patientIntake = PatientIntake::findOrFail($id);
        $data = $this->transformRequestData($request->all());

        $patientIntake->update($data);

        return response()->json([
            'success' => true,
            'data' => $patientIntake,
            'message' => 'Patient intake record updated successfully'
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $patientIntake = PatientIntake::findOrFail($id);
        
        // Check if there are associated billing payments
        if ($patientIntake->billingPayments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete patient intake record with associated billing payments'
            ], 422);
        }

        $patientIntake->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patient intake record deleted successfully'
        ]);
    }

    private function transformRequestData(array $data): array
    {
        return [
            'patient_name' => $data['patient_info']['patientName'] ?? $data['patient_info']['patient_name'],
            'dob' => $data['patient_info']['dob'],
            'gender' => $data['patient_info']['gender'],
            'member_id' => $data['patient_info']['memberID'] ?? $data['patient_info']['member_id'],
            'phone' => $data['patient_info']['phone'],
            'address' => $data['patient_info']['address'],
            'date_of_service' => $data['patient_info']['dateOfService'] ?? $data['patient_info']['date_of_service'],
            
            'insurance' => $data['selection_info']['insurance'],
            'vendor' => $data['selection_info']['vendor'],
            
            'primary_physician' => $data['physician_info']['primaryPhysician'] ?? $data['physician_info']['primary_physician'] ?? null,
            'physician_npi' => $data['physician_info']['physicianNPI'] ?? $data['physician_info']['physician_npi'] ?? null,
            'prescribing_provider' => $data['physician_info']['prescribingProvider'] ?? $data['physician_info']['prescribing_provider'] ?? null,
            
            'diagnosis_icd10' => $data['clinical_info']['diagnosisICD10'] ?? $data['clinical_info']['diagnosis_icd10'] ?? null,
            'date_of_prescription' => $data['clinical_info']['dateOfPrescription'] ?? $data['clinical_info']['date_of_prescription'] ?? null,
            'dme_items' => $data['clinical_info']['dmeItems'] ?? $data['clinical_info']['dme_items'],
            'number_of_items' => $data['clinical_info']['numberOfItems'] ?? $data['clinical_info']['number_of_items'] ?? 1,
            'hcpcs_codes' => $data['clinical_info']['hcpcsCodes'] ?? $data['clinical_info']['hcpcs_codes'],
            'medical_necessity_yn' => $data['clinical_info']['medicalNecessityYN'] ?? $data['clinical_info']['medical_necessity_yn'] ?? false,
            'prior_auth_yn' => $data['clinical_info']['priorAuthYN'] ?? $data['clinical_info']['prior_auth_yn'] ?? false,
            'auth_number' => $data['clinical_info']['authNumber'] ?? $data['clinical_info']['auth_number'] ?? null,
            
            'date_of_shipment' => $data['delivery_tracking']['dateOfShipment'] ?? $data['delivery_tracking']['date_of_shipment'] ?? null,
            'estimated_delivery_date' => $data['delivery_tracking']['estimatedDeliveryDate'] ?? $data['delivery_tracking']['estimated_delivery_date'] ?? null,
            'carrier_service' => $data['delivery_tracking']['carrierService'] ?? $data['delivery_tracking']['carrier_service'] ?? null,
            'tracking_number' => $data['delivery_tracking']['trackingNumber'] ?? $data['delivery_tracking']['tracking_number'] ?? null,
            'proof_of_delivery' => $data['delivery_tracking']['proofOfDelivery'] ?? $data['delivery_tracking']['proof_of_delivery'] ?? null,
            'additional_notes' => $data['delivery_tracking']['additionalNotes'] ?? $data['delivery_tracking']['additional_notes'] ?? null,
        ];
    }

    public function getNextEnrollmentId()
    {
        // Get the last enrollment ID and increment
        $lastEnrollment = PatientIntake::orderBy('enrollment_id', 'desc')->first();
        
        if ($lastEnrollment) {
            $lastNumber = (int) substr($lastEnrollment->enrollment_id, 2);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        $enrollmentId = 'FM' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        return response()->json(['enrollment_id' => $enrollmentId]);
    }
}