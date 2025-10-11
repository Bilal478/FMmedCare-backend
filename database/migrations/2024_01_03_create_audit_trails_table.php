<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE VIEW audit_trail AS
            SELECT 
                bp.id,
                -- Patient Info
                pi.patient_name as patient_name,
                pi.member_id as mrn_member_id,
                pi.enrollment_id as enrollment_id,
                pi.dob as dob,
                
                -- Clinical Info
                pi.diagnosis_icd10 as diagnosis_icd10,
                pi.dme_items as dme_item,
                JSON_UNQUOTE(JSON_EXTRACT(pi.hcpcs_codes, '$[0]')) as hcpcs,
                
                -- Billing Info
                pi.date_of_service as date_of_service,
                bp.billing_status as billing_status,
                '' as modifiers,
                (bp.total_claim_amount - (bp.insurance_paid + 0)) as billed_amount,
                
                -- Payer Info
                pi.insurance as payer_name,
                pi.member_id as policy_member_id,
                pi.prior_auth_yn as auth_required_yn,
                pi.auth_number as auth_number,
                bp.insurance_paid as insurance_paid,
                
                -- Patient Pay Info
                bp.patient_responsibility as patient_responsibility,
                0 as patient_paid,
                bp.patient_responsibility as balance_due,
                bp.date_paid as dates_paid,
                
                -- Patient Facing
                NULL as statement_sent,
                FALSE as payment_plan_yn,
                NULL as payment_plan_terms,
                bp.notes as notes,
                
                -- Audit Trail
                bp.claim_number as claim_number,
                bp.date_claim_submission as date_claim_submitted,
                NULL as adjustments_denials,
                NULL as staff_initials,
                
                bp.created_at,
                bp.updated_at
            FROM billing_payments bp
            LEFT JOIN patient_intakes pi ON bp.patient_intake_id = pi.id
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS audit_trail');
    }
};