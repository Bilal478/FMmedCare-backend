<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillingPaymentRequest;
use App\Models\BillingPayment;
use App\Models\PatientIntake;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillingPaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BillingPayment::with('patientIntake');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('enrollment_id', 'like', "%{$search}%")
                  ->orWhere('claim_number', 'like', "%{$search}%")
                  ->orWhere('member_id', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('billing_status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date_paid', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date_paid', '<=', $request->date_to);
        }

        $perPage = $request->per_page ?? 15;
        $billingPayments = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $billingPayments->items(),
            'pagination' => [
                'current_page' => $billingPayments->currentPage(),
                'per_page' => $billingPayments->perPage(),
                'total' => $billingPayments->total(),
                'last_page' => $billingPayments->lastPage(),
            ],
            'message' => 'Billing payments retrieved successfully'
        ]);
    }

    public function store(BillingPaymentRequest $request): JsonResponse
    {
        // Find patient intake by enrollment_id to set patient_intake_id
        if ($request->has('enrollment_id')) {
            $patientIntake = PatientIntake::where('enrollment_id', $request->enrollment_id)->first();
            if ($patientIntake) {
                $request->merge(['patient_intake_id' => $patientIntake->id]);
            }
        }

        $billingPayment = BillingPayment::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $billingPayment->load('patientIntake'),
            'message' => 'Billing payment record created successfully'
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $billingPayment = BillingPayment::with('patientIntake')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $billingPayment,
            'message' => 'Billing payment record retrieved successfully'
        ]);
    }

    public function update(BillingPaymentRequest $request, $id): JsonResponse
    {
        $billingPayment = BillingPayment::findOrFail($id);
        
        // Prevent updating non-editable fields if they're being changed
        $nonEditableFields = ['patient_name', 'enrollment_id', 'member_id', 'dme_item', 'hcpcs', 'payer'];
        foreach ($nonEditableFields as $field) {
            if ($request->has($field) && $billingPayment->$field != $request->$field) {
                return response()->json([
                    'success' => false,
                    'message' => "Field '{$field}' is not editable"
                ], 422);
            }
        }

        $billingPayment->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $billingPayment->load('patientIntake'),
            'message' => 'Billing payment record updated successfully'
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $billingPayment = BillingPayment::findOrFail($id);
        $billingPayment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Billing payment record deleted successfully'
        ]);
    }
}