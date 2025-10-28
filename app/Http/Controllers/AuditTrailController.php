<?php

namespace App\Http\Controllers;

use App\Models\PatientIntake;
use App\Models\BillingPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PatientIntake::with(['billingPayments']);

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('enrollment_id', 'like', "%{$search}%")
                  ->orWhere('member_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Insurance filter
        if ($request->has('insurance') && $request->insurance) {
            $query->where('insurance', $request->insurance);
        }

        // Billing status filter (based on payment status)
        if ($request->has('billing_status') && $request->billing_status) {
            $query->whereHas('billingPayments', function ($q) use ($request) {
                $q->where('billing_status', $request->billing_status);
            });
        }

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->where('date_of_service', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date_of_service', '<=', $request->date_to);
        }

        $perPage = $request->per_page ?? 10;
        $patientIntakes = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Transform data with calculated fields
        $transformedData = $patientIntakes->getCollection()->map(function ($patientIntake) {
            return $this->transformPatientIntakeData($patientIntake);
        });

        return response()->json([
            'success' => true,
            'data' => $transformedData,
            'pagination' => [
                'current_page' => $patientIntakes->currentPage(),
                'per_page' => $patientIntakes->perPage(),
                'total' => $patientIntakes->total(),
                'last_page' => $patientIntakes->lastPage(),
            ],
            'message' => 'Audit trail records retrieved successfully'
        ]);
    }

    public function show($id): JsonResponse
    {
        $patientIntake = PatientIntake::with(['billingPayments'])->findOrFail($id);
        
        $transformedData = $this->transformPatientIntakeData($patientIntake);

        return response()->json([
            'success' => true,
            'data' => $transformedData,
            'message' => 'Patient intake audit record retrieved successfully'
        ]);
    }

    public function updatePayment(Request $request, $paymentId): JsonResponse
    {
        $billingPayment = BillingPayment::findOrFail($paymentId);

        $validated = $request->validate([
            'billing_status' => 'sometimes|in:Submitted,Pending,Denied,Paid,In Process',
            'total_claim_amount' => 'sometimes|numeric|min:0',
            'allowed_amount' => 'sometimes|numeric|min:0',
            'insurance_paid' => 'sometimes|numeric|min:0',
            'date_paid' => 'sometimes|date|nullable',
            'is_paid' => 'sometimes|in:Yes,No,Partial',
            'notes' => 'sometimes|string',
            'date_claim_submission' => 'sometimes|date|nullable',
            'claim_number' => 'sometimes|string|max:100|nullable'
        ]);

        $billingPayment->update($validated);

        return response()->json([
            'success' => true,
            'data' => $billingPayment->fresh(),
            'message' => 'Billing payment updated successfully'
        ]);
    }

    // public function export(Request $request): JsonResponse
    // {
    //     $query = PatientIntake::with(['billingPayments']);

    //     if ($request->has('date_from') && $request->date_from) {
    //         $query->where('date_of_service', '>=', $request->date_from);
    //     }

    //     if ($request->has('date_to') && $request->date_to) {
    //         $query->where('date_of_service', '<=', $request->date_to);
    //     }

    //     if ($request->has('status') && $request->status) {
    //         $query->whereHas('billingPayments', function ($q) use ($request) {
    //             $q->where('billing_status', $request->status);
    //         });
    //     }

    //     $patientIntakes = $query->orderBy('created_at', 'desc')->get();

    //     $transformedData = $patientIntakes->map(function ($patientIntake) {
    //         return $this->transformPatientIntakeData($patientIntake);
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'data' => $transformedData,
    //         'message' => 'Audit trail export completed successfully'
    //     ]);
    // }
    public function export(Request $request): JsonResponse
    {
        $query = PatientIntake::with(['billingPayments']);

        // Date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->where('date_of_service', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date_of_service', '<=', $request->date_to);
        }

        // Billing status filter
        if ($request->has('status') && $request->status) {
            $query->whereHas('billingPayments', function ($q) use ($request) {
                $q->where('billing_status', $request->status);
            });
        }

        // Insurance filter
        if ($request->has('insurance') && $request->insurance) {
            $query->where('insurance', $request->insurance);
        }

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('enrollment_id', 'like', "%{$search}%")
                  ->orWhere('member_id', 'like', "%{$search}%");
            });
        }

        $patientIntakes = $query->orderBy('created_at', 'desc')->get();

        $transformedData = $patientIntakes->map(function ($patientIntake) {
            return $this->transformPatientIntakeData($patientIntake);
        });

        return response()->json([
            'success' => true,
            'data' => $transformedData,
            'message' => 'Audit trail export completed successfully'
        ]);
    }

    private function transformPatientIntakeData(PatientIntake $patientIntake): array
    {
        $billingPayments = $patientIntake->billingPayments;

        // Calculate totals
        $totalBilledAmount = $billingPayments->sum('total_claim_amount');
        $totalInsurancePaid = $billingPayments->sum('insurance_paid');
        $totalPatientResponsibility = $billingPayments->sum('patient_responsibility');
        $totalBalanceDue = $totalBilledAmount - $totalInsurancePaid;

        // Determine overall billing status
        $overallBillingStatus = $this->calculateOverallBillingStatus($billingPayments);

        return [
            'patient_intake_id' => $patientIntake->id,
            'enrollment_id' => $patientIntake->enrollment_id,
            'patient_name' => $patientIntake->patient_name,
            'dob' => $patientIntake->dob,
            'gender' => $patientIntake->gender,
            'member_id' => $patientIntake->member_id,
            'phone' => $patientIntake->phone,
            'address' => $patientIntake->address,
            'date_of_service' => $patientIntake->date_of_service,
            'insurance' => $patientIntake->insurance,
            'vendor' => $patientIntake->vendor,
            'primary_physician' => $patientIntake->primary_physician,
            'physician_npi' => $patientIntake->physician_npi,
            'prescribing_provider' => $patientIntake->prescribing_provider,
            'diagnosis_icd10' => $patientIntake->diagnosis_icd10,
            'date_of_prescription' => $patientIntake->date_of_prescription,
            'dme_items' => $patientIntake->dme_items,
            'number_of_items' => $patientIntake->number_of_items,
            'hcpcs_codes' => $patientIntake->hcpcs_codes,
            'medical_necessity_yn' => $patientIntake->medical_necessity_yn,
            'prior_auth_yn' => $patientIntake->prior_auth_yn,
            'auth_number' => $patientIntake->auth_number,
            'date_of_shipment' => $patientIntake->date_of_shipment,
            'estimated_delivery_date' => $patientIntake->estimated_delivery_date,
            'carrier_service' => $patientIntake->carrier_service,
            'tracking_number' => $patientIntake->tracking_number,
            'proof_of_delivery' => $patientIntake->proof_of_delivery,
            'additional_notes' => $patientIntake->additional_notes,
            'billing_payments' => $billingPayments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'billing_status' => $payment->billing_status,
                    'total_claim_amount' => $payment->total_claim_amount,
                    'allowed_amount' => $payment->allowed_amount,
                    'insurance_paid' => $payment->insurance_paid,
                    'patient_responsibility' => $payment->patient_responsibility,
                    'total_paid_balance' => $payment->total_paid_balance,
                    'date_paid' => $payment->date_paid,
                    'is_paid' => $payment->is_paid,
                    'claim_number' => $payment->claim_number,
                    'date_claim_submission' => $payment->date_claim_submission,
                    'notes' => $payment->notes,
                    'created_at' => $payment->created_at,
                    'updated_at' => $payment->updated_at
                ];
            }),
            'total_billed_amount' => (float) $totalBilledAmount,
            'total_insurance_paid' => (float) $totalInsurancePaid,
            'total_patient_responsibility' => (float) $totalPatientResponsibility,
            'total_balance_due' => (float) $totalBalanceDue,
            'overall_billing_status' => $overallBillingStatus,
            'created_at' => $patientIntake->created_at,
            'updated_at' => $patientIntake->updated_at
        ];
    }

    private function calculateOverallBillingStatus($billingPayments): string
    {
        if ($billingPayments->isEmpty()) {
            return 'No Claims';
        }

        $statusCounts = $billingPayments->countBy('billing_status');
        $totalPayments = $billingPayments->count();

        // If all payments are paid
        if ($statusCounts->get('Paid', 0) === $totalPayments) {
            return 'Paid';
        }

        // If any payment is denied
        if ($statusCounts->get('Denied', 0) > 0) {
            return 'Denied';
        }

        // If any payment is paid but not all
        if ($statusCounts->get('Paid', 0) > 0) {
            return 'Partial';
        }

        // Default to the most common status
        return $statusCounts->sortDesc()->keys()->first() ?? 'Pending';
    }
}