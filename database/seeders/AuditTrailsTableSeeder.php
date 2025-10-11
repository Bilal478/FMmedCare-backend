<?php

namespace Database\Seeders;

use App\Models\AuditTrail;
use App\Models\PatientIntake;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AuditTrailsTableSeeder extends Seeder
{
    public function run()
    {
        $users = User::pluck('id')->toArray();
        $patients = PatientIntake::pluck('patient_name', 'id')->toArray();
        
        $billingStatuses = ['Pending', 'Submitted', 'Paid', 'Denied', 'Appealed'];
        $payers = ['UnitedHealthcare', 'Blue Cross Blue Shield', 'Aetna', 'Cigna', 'Medicare'];
        $staffInitials = ['JD', 'SM', 'RK', 'TL', 'MP', 'JW', 'SG', 'BD'];

        $auditTrails = [];

        for ($i = 1; $i <= 200; $i++) {
            $patientName = $patients[array_rand($patients)];
            $dateOfService = Carbon::now()->subDays(rand(1, 365));
            $billedAmount = rand(500, 5000) + (rand(0, 99) / 100);
            $insurancePaid = rand(0, 1) ? $billedAmount * (rand(70, 95) / 100) : 0;
            $patientResponsibility = $billedAmount - $insurancePaid;
            $patientPaid = rand(0, 1) ? $patientResponsibility * (rand(50, 100) / 100) : 0;
            $balanceDue = $patientResponsibility - $patientPaid;

            $datesPaid = [];
            if ($patientPaid > 0) {
                $paymentCount = rand(1, 3);
                for ($j = 0; $j < $paymentCount; $j++) {
                    $datesPaid[] = $dateOfService->copy()->addDays(rand(30, 90) + ($j * 30))->format('Y-m-d');
                }
            }

            $auditTrails[] = [
                'patient_name' => $patientName,
                'mrn' => 'MRN' . str_pad(rand(100000, 999999), 8, '0', STR_PAD_LEFT),
                'patient_dob' => Carbon::now()->subYears(rand(25, 85))->subDays(rand(0, 365)),
                'diagnosis_icd10' => $this->getRandomDiagnosisCode(),
                'dme_item' => $this->getRandomDmeItem(),
                'hcpcs' => $this->getRandomHcpcsCode(),
                'date_of_service' => $dateOfService,
                'billing_status' => $billingStatuses[array_rand($billingStatuses)],
                'modifiers' => rand(0, 1) ? $this->getRandomModifiers() : null,
                'billed_amount' => $billedAmount,
                'payer_name' => $payers[array_rand($payers)],
                'policy_id' => 'POL' . str_pad(rand(100000, 999999), 8, '0', STR_PAD_LEFT),
                'auth_required_yn' => rand(0, 1),
                'auth_number' => rand(0, 1) ? 'AUTH' . str_pad(rand(1000, 9999), 6, '0', STR_PAD_LEFT) : null,
                'insurance_paid' => $insurancePaid,
                'patient_responsibility' => $patientResponsibility,
                'patient_paid' => $patientPaid,
                'balance_due' => $balanceDue,
                'dates_paid' => !empty($datesPaid) ? $datesPaid : null,
                'statement_sent' => rand(0, 1),
                'payment_plan_yn' => $balanceDue > 0 ? rand(0, 1) : false,
                'claim_number' => 'CLM' . str_pad(rand(100000, 999999), 8, '0', STR_PAD_LEFT),
                'date_claim_submitted' => $dateOfService->copy()->addDays(rand(1, 14)),
                'adjustments_denials' => rand(0, 1) ? $this->getAdjustmentNotes() : null,
                'staff_initials' => $staffInitials[array_rand($staffInitials)],
                'notes' => $this->getAuditNotes(),
                'created_by' => $users[array_rand($users)],
                'created_at' => $dateOfService->copy()->addDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 100)),
            ];
        }

        foreach ($auditTrails as $audit) {
            AuditTrail::create($audit);
        }
    }

    private function getRandomDiagnosisCode()
    {
        $diagnoses = ['E11.9', 'I10', 'J44.9', 'M54.50', 'E78.5', 'I25.10', 'J45.909', 'M17.9'];
        return $diagnoses[array_rand($diagnoses)];
    }

    private function getRandomDmeItem()
    {
        $items = ['CPAP Machine', 'Oxygen Concentrator', 'Wheelchair', 'Hospital Bed', 'Nebulizer', 'Blood Glucose Monitor'];
        return $items[array_rand($items)];
    }

    private function getRandomHcpcsCode()
    {
        $codes = ['E0601', 'E1390', 'K0001', 'E0250', 'E0570', 'E0607', 'E0784', 'E0143'];
        return $codes[array_rand($codes)];
    }

    private function getRandomModifiers()
    {
        $modifiers = ['RR', 'KX', 'GA', 'GY', 'GZ', 'LT', 'RT'];
        $selected = array_rand($modifiers, rand(1, 2));
        if (is_array($selected)) {
            return implode(', ', array_map(fn($idx) => $modifiers[$idx], $selected));
        }
        return $modifiers[$selected];
    }

    private function getAdjustmentNotes()
    {
        $notes = [
            'Adjusted for contractual obligation',
            'Denied - medical necessity not established',
            'Duplicate claim adjustment',
            'Timely filing denial appealed',
            'Bundled service adjustment',
            'Patient responsibility adjusted per policy'
        ];
        return $notes[array_rand($notes)];
    }

    private function getAuditNotes()
    {
        $notes = [
            'Routine audit - no issues found',
            'Verified documentation complete',
            'Follow-up required for missing signature',
            'Coding verified accurate',
            'Medical necessity documentation reviewed',
            'Prior authorization on file',
            'Patient demographic information verified'
        ];
        return $notes[array_rand($notes)];
    }
}