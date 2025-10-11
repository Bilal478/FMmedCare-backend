<?php

namespace App\Http\Controllers;

use App\Models\PatientIntake;
use App\Models\BillingPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $totalPatients = PatientIntake::count();
        $totalBillingRecords = BillingPayment::count();
        $totalRevenue = BillingPayment::sum('insurance_paid');
        $pendingClaims = BillingPayment::where('billing_status', 'Pending')->count();
        $paidClaims = BillingPayment::where('billing_status', 'Paid')->count();
        $outstandingBalance = BillingPayment::sum('patient_responsibility');

        return response()->json([
            'success' => true,
            'data' => [
                'totalPatients' => $totalPatients,
                'totalBillingRecords' => $totalBillingRecords,
                'totalRevenue' => (float) $totalRevenue,
                'pendingClaims' => $pendingClaims,
                'paidClaims' => $paidClaims,
                'outstandingBalance' => (float) $outstandingBalance,
            ],
            'message' => 'Dashboard statistics retrieved successfully'
        ]);
    }

    public function recentActivity(Request $request): JsonResponse
    {
        $limit = $request->limit ?? 10;

        $patientActivities = PatientIntake::latest()->take($limit)->get();
        $billingActivities = BillingPayment::with('patientIntake')->latest()->take($limit)->get();

        $recentActivities = $patientActivities->concat($billingActivities)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();

        return response()->json([
            'success' => true,
            'data' => $recentActivities,
            'message' => 'Recent activity retrieved successfully'
        ]);
    }
}