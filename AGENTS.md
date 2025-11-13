# AGENTS Domain Outline

## Project Overview
This platform streamlines academic operations across student services, teaching workflows, class logistics, attendance monitoring, and manual payment processing. The MVP targets cohesive experiences for students, teachers, registrars, and finance staff by unifying data, automating repetitive tasks, and providing actionable insights.

## Use Laravel Way
When scaffolding new application code, prefer the relevant `php artisan make:*` generators (for example, `php artisan make:model Example -mfs` or `php artisan make:controller Example --resource`) so new classes follow Laravel conventions.

## Module Map
- `Students`: Enrollment management, guardianship, academic standing.
- `Teachers`: Assignment, qualifications, availability, performance feedback.
- `Classes`: Scheduling, curriculum alignment, capacity management.
- `Attendance`: Session tracking, absence alerts, compliance reporting.
- `Manual Payments`: Offline tuition handling, reconciliation, receipting.

## Core Data Entities
- `Student`: profile, contact info, guardians, enrollment history, balances.
- `Teacher`: profile, certifications, subject specialties, employment status.
- `Class`: course metadata, term, schedule, roster, capacity, primary teacher.
- `AttendanceRecord`: class session, participant, status, note, timestamp source.
- `ManualPayment`: payer, student, amount, method, receipt, ledger reference.
- `ManualPaymentBatch`: collection metadata, cashier, totals, reconciliation status.

## User Roles
- `Student`: view schedule, attendance history, balances, receipts.
- `Teacher`: manage class rosters, record attendance, review payments applied to students they teach.
- `Registrar/Admin`: create classes, manage enrollments, override attendance, audit manual payments.
- `Finance`: process manual payments, reconcile batches, generate financial reports.

## Prioritized MVP Features
1. **Student Portal**
   - Secure login and profile view.
   - Current class schedule overview.
   - Attendance history with status explanations.
   - Outstanding balance summary with manual payment receipts.
2. **Teacher Workspace**
   - Class roster management per term.
   - Rapid attendance entry (bulk present toggle, absence reasons).
   - Alerts for attendance anomalies (e.g., consecutive absences).
3. **Class Management**
   - CRUD for classes with schedule and capacity checks.
   - Enrollment assignment with conflict detection.
   - Roster exports for offline use.
4. **Attendance Operations**
   - Session-based attendance recording with audit trail.
   - Absence notification triggers (email/SMS hooks).
   - Compliance dashboard summarizing attendance rates.
5. **Manual Payment Flow**
   - Intake form capturing payer, student, amount, method.
   - Batch reconciliation workflow with status transitions.
   - Receipt generation and balance updates.
   - Audit log of adjustments and reversals.

