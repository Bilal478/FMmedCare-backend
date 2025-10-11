<?php

namespace Database\Seeders;

use App\Models\PatientIntake;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PatientIntakesTableSeeder extends Seeder
{
    public function run()
    {
        $users = User::pluck('id')->toArray();
        $genders = ['Male', 'Female', 'Other'];
        
        $insuranceProviders = [
            'UnitedHealthcare', 'Blue Cross Blue Shield', 'Aetna', 'Cigna', 
            'Humana', 'Kaiser Permanente', 'Medicare', 'Medicaid'
        ];

        $physicians = [
            'Dr. Michael Chen', 'Dr. Jennifer Martinez', 'Dr. Robert Wilson',
            'Dr. Lisa Anderson', 'Dr. James Thompson', 'Dr. Maria Garcia'
        ];

        $diagnoses = [
            'E11.9' => 'Type 2 diabetes mellitus without complications',
            'I10' => 'Essential (primary) hypertension',
            'J44.9' => 'Chronic obstructive pulmonary disease, unspecified',
            'M54.50' => 'Low back pain, unspecified',
            'E78.5' => 'Hyperlipidemia, unspecified',
            'I25.10' => 'Atherosclerotic heart disease of native coronary artery without angina pectoris',
            'J45.909' => 'Unspecified asthma, uncomplicated',
            'M17.9' => 'Osteoarthritis of knee, unspecified'
        ];

        $dmeItems = [
            'CPAP Machine', 'Oxygen Concentrator', 'Wheelchair', 'Hospital Bed',
            'Nebulizer', 'Blood Glucose Monitor', 'Insulin Pump', 'Walkers',
            'Crutches', 'Patient Lift', 'Compression Stockings', 'Oxygen Tank'
        ];

        $hcpcsCodes = [
            'E0601' => 'Continuous positive airway pressure (CPAP) device',
            'E1390' => 'Oxygen concentrator',
            'K0001' => 'Standard wheelchair',
            'E0250' => 'Hospital bed',
            'E0570' => 'Nebulizer',
            'E0607' => 'Home blood glucose monitor',
            'E0784' => 'Ambulatory insulin pump',
            'E0143' => 'Walker'
        ];

        $patientIntakes = [];

        for ($i = 1; $i <= 50; $i++) {
            $gender = $genders[array_rand($genders)];
            $firstName = $gender === 'Male' ? $this->getMaleFirstName() : $this->getFemaleFirstName();
            $lastName = $this->getLastName();
            $patientName = $firstName . ' ' . $lastName;

            $diagnosisCode = array_rand($diagnoses);
            $dmeItem = $dmeItems[array_rand($dmeItems)];
            $hcpcsCode = array_rand($hcpcsCodes);

            $patientIntakes[] = [
                'patient_name' => $patientName,
                'dob' => Carbon::now()->subYears(rand(25, 85))->subDays(rand(0, 365)),
                'gender' => $gender,
                'patient_id' => 'PID' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'phone' => $this->generatePhoneNumber(),
                'address' => $this->generateAddress(),
                'insurance_provider' => $insuranceProviders[array_rand($insuranceProviders)],
                'policy_id' => 'POL' . str_pad(rand(100000, 999999), 8, '0', STR_PAD_LEFT),
                'primary_physician' => $physicians[array_rand($physicians)],
                'npi' => str_pad(rand(1000000000, 9999999999), 10, '0', STR_PAD_LEFT),
                'prescribing_provider' => $physicians[array_rand($physicians)],
                'diagnosis_icd10' => $diagnosisCode,
                'prescription_date' => Carbon::now()->subDays(rand(1, 90)),
                'dme_items' => $dmeItem,
                'hcpcs' => $hcpcsCode,
                'medical_necessity_yn' => rand(0, 1),
                'prior_auth_yn' => rand(0, 1),
                'auth_number' => rand(0, 1) ? 'AUTH' . str_pad(rand(1000, 9999), 6, '0', STR_PAD_LEFT) : null,
                'delivery_date' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 30)) : null,
                'setup_by' => rand(0, 1) ? ['Tech Support', 'Nurse Johnson', 'Therapist Smith'][array_rand([0, 1, 2])] : null,
                'trained_yn' => rand(0, 1),
                'patient_signature_yn' => rand(0, 1),
                'notes' => rand(0, 1) ? 'Patient demonstrated proper use of equipment. Follow-up scheduled in 30 days.' : null,
                'created_by' => $users[array_rand($users)],
                'created_at' => Carbon::now()->subDays(rand(1, 365)),
                'updated_at' => Carbon::now()->subDays(rand(0, 100)),
            ];
        }

        foreach ($patientIntakes as $intake) {
            PatientIntake::create($intake);
        }
    }

    private function getMaleFirstName()
    {
        $names = ['James', 'John', 'Robert', 'Michael', 'William', 'David', 'Richard', 'Joseph', 'Thomas', 'Charles'];
        return $names[array_rand($names)];
    }

    private function getFemaleFirstName()
    {
        $names = ['Mary', 'Patricia', 'Jennifer', 'Linda', 'Elizabeth', 'Barbara', 'Susan', 'Jessica', 'Sarah', 'Karen'];
        return $names[array_rand($names)];
    }

    private function getLastName()
    {
        $names = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];
        return $names[array_rand($names)];
    }

    private function generatePhoneNumber()
    {
        return sprintf('(%03d) %03d-%04d', rand(200, 999), rand(200, 999), rand(1000, 9999));
    }

    private function generateAddress()
    {
        $streets = ['Main St', 'Oak Ave', 'Maple Dr', 'Elm St', 'Pine St', 'Cedar Ln', 'Birch Rd', 'Willow Way'];
        $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego'];
        $states = ['NY', 'CA', 'IL', 'TX', 'AZ', 'PA', 'TX', 'CA'];
        
        $index = array_rand($cities);
        return rand(100, 9999) . ' ' . $streets[array_rand($streets)] . ', ' . 
               $cities[$index] . ', ' . $states[$index] . ' ' . rand(10000, 99999);
    }
}