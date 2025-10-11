<?php

namespace Database\Seeders;

use App\Models\PaymentsBilling;
use App\Models\PatientIntake;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PaymentsBillingTableSeeder extends Seeder
{
    public function run()
    {
        $users = User::pluck('id')->toArray();
        $patients = PatientIntake::pluck('patient_name', 'patient_id')->toArray();
        
        $payers = [
            'UnitedHealthcare', 'Blue Cross Blue Shield', 'Aetna', 'Cigna', 
            'Humana', 'Kaiser Permanente', 'Medicare', 'Medicaid', 'Self Pay'
        ];

        $dmeItems = [
            'CPAP Machine' => 1200.00,
            'Oxygen Concentrator' => 850.00,
            'Wheelchair' => 650.00,
            'Hospital Bed' => 2200.00,
            'Nebulizer' => 150.00,
            'Blood Glucose Monitor' => 75.00,
            'Insulin Pump' => 4500.00,
            'Walkers' => 120.00
        ];

        $billingStatuses = ['Pending', 'Submitted', 'Paid', 'Denied', 'Appealed'];

        $payments = [];

        for ($i = 1; $i <= 100; $i++) {
            $patientId = array_rand($patients);
            $patientName = $patients[$patientId];
            $dmeItem = array_rand($dmeItems);
            $totalCharge = $dmeItems[$dmeItem] * (rand(80, 120) / 100); // ±20% variation
            
            $billingStatus = $billingStatuses[array_rand($billingStatuses)];
            $isPaid = $billingStatus === 'Paid';
            
            $insurancePaid = 0;
            $patientPaid = 0;
            $datePaid = null;

            if ($isPaid) {
                $insurancePaid = $totalCharge * (rand(70, 90) / 100);
                $patientPaid = $totalCharge - $insurancePaid;
                $datePaid = Carbon::now()->subDays(rand(1, 60));
            } elseif ($billingStatus === 'Submitted') {
                $insurancePaid = $totalCharge * (rand(0, 50) / 100);
                $patientPaid = $totalCharge * (rand(0, 20) / 100);
            }

            $dateOfService = Carbon::now()->subDays(rand(30, 365));
            $dateClaimSubmission = $dateOfService->copy()->addDays(rand(1, 14));

            $payments[] = [
                'patient_name' => $patientName,
                'dme_item' => $dmeItem,
                'hcpcs' => $this->getHcpcsForDmeItem($dmeItem),
                'payer' => $payers[array_rand($payers)],
                'total_charge' => $totalCharge,
                'insurance_paid' => $insurancePaid,
                'patient_paid' => $patientPaid,
                'date_paid' => $datePaid,
                'authorization_yn' => rand(0, 1),
                'billing_status' => $billingStatus,
                'date_of_service' => $dateOfService,
                'date_claim_submission' => $dateClaimSubmission,
                'claim_number' => 'CLM' . str_pad(rand(100000, 999999), 8, '0', STR_PAD_LEFT),
                'notes' => $this->getBillingNotes($billingStatus),
                'created_by' => $users[array_rand($users)],
                'created_at' => $dateClaimSubmission,
                'updated_at' => Carbon::now()->subDays(rand(0, 30)),
            ];
        }

        foreach ($payments as $payment) {
            PaymentsBilling::create($payment);
        }
    }

    private function getHcpcsForDmeItem($dmeItem)
    {
        $mapping = [
            'CPAP Machine' => 'E0601',
            'Oxygen Concentrator' => 'E1390',
            'Wheelchair' => 'K0001',
            'Hospital Bed' => 'E0250',
            'Nebulizer' => 'E0570',
            'Blood Glucose Monitor' => 'E0607',
            'Insulin Pump' => 'E0784',
            'Walkers' => 'E0143'
        ];

        return $mapping[$dmeItem] ?? 'E9999';
    }

    private function getBillingNotes($status)
    {
        $notes = [
            'Pending' => 'Awaiting provider signature before submission.',
            'Submitted' => 'Claim submitted to insurance. Awaiting response.',
            'Paid' => 'Payment received. Patient balance notified.',
            'Denied' => 'Claim denied. Review required for appeal.',
            'Appealed' => 'Appeal submitted with additional documentation.'
        ];

        return $notes[$status] ?? 'No additional notes.';
    }
}