<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\BillingPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditTrailController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AuditTrail::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_name', 'like', "%{$search}%")
                  ->orWhere('enrollment_id', 'like', "%{$search}%")
                  ->orWhere('claim_number', 'like', "%{$search}%")
                  ->orWhere('mrn_member_id', 'like', "%{$search}%");
            });
        }

        if ($request->has('patient_name') && $request->patient_name) {
            $query->where('patient_name', 'like', "%{$request->patient_name}%");
        }

        if ($request->has('claim_number') && $request->claim_number) {
            $query->where('claim_number', 'like', "%{$request->claim_number}%");
        }

        if ($request->has('status') && $request->status) {
            $query->where('billing_status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date_of_service', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date_of_service', '<=', $request->date_to);
        }

        $perPage = $request->per_page ?? 15;
        $auditTrails = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $auditTrails->items(),
            'pagination' => [
                'current_page' => $auditTrails->currentPage(),
                'per_page' => $auditTrails->perPage(),
                'total' => $auditTrails->total(),
                'last_page' => $auditTrails->lastPage(),
            ],
            'message' => 'Audit trail records retrieved successfully'
        ]);
    }

    public function show($id): JsonResponse
    {
        $auditTrail = AuditTrail::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $auditTrail,
            'message' => 'Audit trail record retrieved successfully'
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        // Since audit_trail is a view, we update the underlying billing_payments record
        $billingPayment = BillingPayment::findOrFail($id);

        // Only allow updating specific fields that are editable in Module 2
        $editableFields = [
            'total_claim_amount', 'allowed_amount', 'insurance_paid', 'date_paid',
            'is_paid', 'notes', 'billing_status', 'date_claim_submission', 'claim_number'
        ];

        $updateData = [];
        foreach ($editableFields as $field) {
            if ($request->has($field)) {
                $updateData[$field] = $request->$field;
            }
        }

        $billingPayment->update($updateData);

        return response()->json([
            'success' => true,
            'data' => $billingPayment->fresh(),
            'message' => 'Audit trail record updated successfully'
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $query = AuditTrail::query();

        if ($request->has('date_from') && $request->date_from) {
            $query->where('date_of_service', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('date_of_service', '<=', $request->date_to);
        }

        if ($request->has('status') && $request->status) {
            $query->where('billing_status', $request->status);
        }

        $auditTrails = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $auditTrails,
            'message' => 'Audit trail export completed successfully'
        ]);
    }
}