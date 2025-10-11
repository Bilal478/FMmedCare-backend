<?php

namespace Database\Factories;

use App\Models\PatientIntake;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientIntakeFactory extends Factory
{
    protected $model = PatientIntake::class;

    public function definition()
    {
        $gender = $this->faker->randomElement(['Male', 'Female', 'Other']);
        
        return [
            'patient_name' => $this->faker->name($gender),
            'dob' => $this->faker->dateTimeBetween('-85 years', '-18 years'),
            'gender' => $gender,
            'patient_id' => 'PID' . $this->faker->unique()->numerify('########'),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'insurance_provider' => $this->faker->randomElement([
                'UnitedHealthcare', 'Blue Cross Blue Shield', 'Aetna', 'Cigna', 
                'Humana', 'Kaiser Permanente', 'Medicare', 'Medicaid'
            ]),
            'policy_id' => 'POL' . $this->faker->numerify('########'),
            'primary_physician' => 'Dr. ' . $this->faker->firstName() . ' ' . $this->faker->lastName(),
            'npi' => $this->faker->numerify('##########'),
            'prescribing_provider' => 'Dr. ' . $this->faker->firstName() . ' ' . $this->faker->lastName(),
            'diagnosis_icd10' => $this->faker->randomElement(['E11.9', 'I10', 'J44.9', 'M54.50']),
            'prescription_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'dme_items' => $this->faker->randomElement([
                'CPAP Machine', 'Oxygen Concentrator', 'Wheelchair', 'Hospital Bed',
                'Nebulizer', 'Blood Glucose Monitor'
            ]),
            'hcpcs' => $this->faker->randomElement(['E0601', 'E1390', 'K0001', 'E0250', 'E0570']),
            'medical_necessity_yn' => $this->faker->boolean(80),
            'prior_auth_yn' => $this->faker->boolean(60),
            'auth_number' => $this->faker->boolean(50) ? 'AUTH' . $this->faker->numerify('######') : null,
            'delivery_date' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('-6 months', 'now') : null,
            'setup_by' => $this->faker->boolean(60) ? $this->faker->name() : null,
            'trained_yn' => $this->faker->boolean(85),
            'patient_signature_yn' => $this->faker->boolean(90),
            'notes' => $this->faker->boolean(40) ? $this->faker->sentence(10) : null,
            'created_by' => User::factory(),
        ];
    }
}