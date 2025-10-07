# PHASE 1 TESTING CHECKLIST

Use this checklist to validate all Phase 1 fixes and refactors. Check off each item after verification.

1. Setup and Seed
   - [ ] Fresh install: composer install, npm install (if needed), php artisan key:generate
   - [ ] Database migrations run successfully
   - [ ] Seeders run without errors (idempotent re-run)
   - [ ] Admin user exists (admin@darulabrar.com / Admin@2025)
   - [ ] Demo data (departments, classes, users, subjects, exams, results, fees, attendance, notices) created in local

2. Authentication and Dashboard
   - [ ] Login works with admin credentials
   - [ ] Role-based dashboards accessible for admin/teacher/student
   - [ ] Navigation reflects role permissions

3. Routes and Middleware
   - [ ] All protected routes require login
   - [ ] Admin-only routes blocked for non-admin users
   - [ ] Teacher-only routes blocked for non-teacher users
   - [ ] Student-only routes blocked for non-student users
   - [ ] Results routes:
     - [ ] Mark-sheet: GET /results/{exam}/{student}/mark-sheet
     - [ ] Class summary PDF: GET /results/{exam}/class-summary/pdf
   - [ ] Study materials download route works

4. Validation (Form Requests)
   - [ ] Students: Store/Update follow validation rules (unique email/admission_number, file size/type, etc.)
   - [ ] Teachers: Store/Update follow rules (unique email, required fields, image constraints)
   - [ ] Fees: Store/Update follow rules (due date, amount, required conditional fields)
   - [ ] Attendance: Bulk store validates array structure and per-student fields
   - [ ] Exams: Store/Update validates date relations and publishing constraints
   - [ ] Results (Bulk): Validates marks_obtained array, end_date, publish status, and full_mark limits
   - [ ] Notices: Store/Update validation rules apply properly

5. Controllers and Repositories
   - [ ] StudentRepository uses FileUploadService for avatars (upload/delete)
   - [ ] TeacherRepository uses FileUploadService for avatars (upload/delete)
   - [ ] ResultRepository uses marks_obtained and gpa_point consistently
   - [ ] ResultRepository storeBulk calculates and persists grade and gpa_point
   - [ ] AttendanceRepository storeBulk matches controller array contract (studentId-keyed map)
   - [ ] FeeRepository: recordPayment updates status/paid_amount and respects payment_method
   - [ ] Controllers wrap operations with try/catch and log errors

6. Results Module
   - [ ] Bulk entry form submits marks_obtained with remarks (associative by student)
   - [ ] Grade and GPA point are calculated per saved result
   - [ ] Average marks and pass rate statistics load for selected exam+subject
   - [ ] Student’s my results page loads and summarizes properly
   - [ ] Mark-sheet PDF downloads successfully
   - [ ] Class summary PDF downloads successfully
   - [ ] Editing a result respects “published” lock (cannot modify if published)

7. Attendance Module
   - [ ] Bulk store uses studentId => {status, remarks} structure
   - [ ] Attendance records created/updated correctly
   - [ ] My Attendance page (student) loads
   - [ ] Summary counts for present/absent/late are correct

8. Fee Management
   - [ ] Create fee with required fields and validation errors for invalid input
   - [ ] Update fee sets collected_by/payment_date on paid/partial as expected
   - [ ] Record payment accepts paid_amount, payment_method and updates status and totals
   - [ ] Collection report filters by fee_type and date range
   - [ ] Outstanding report respects overdue filter (overdue=true)
   - [ ] Invoice PDF generation works

9. Notices
   - [ ] Create/Edit/Delete works with validation
   - [ ] Public notices route shows active, published, non-expired notices
   - [ ] Audience filtering works (all/students/teachers/staff)

10. Seeders and Idempotency
    - [ ] RolePermissionSeeder uses firstOrCreate and syncPermissions; can re-run safely
    - [ ] AdminUserSeeder uses updateOrCreate and skips in production
    - [ ] DemoDataSeeder computes grade/gpa_point via model and is safe in dev
    - [ ] DatabaseSeeder runs seeders in proper order and prints success

11. Logging and Config
    - [ ] Controllers log errors and info properly
    - [ ] Logging config honors environment (LOG_CHANNEL=stack suggested, LOG_LEVEL=debug for local)
    - [ ] Deprecation warnings (if any) are logged to separate channel (optional)

12. Documentation
    - [ ] README updated with project-specific setup and usage
    - [ ] PHASE1_TESTING_CHECKLIST.md exists and is complete
    - [ ] Notes included for .env variables:
      - APP_NAME="Darul Abrar Madrasa"
      - LOG_CHANNEL=stack
      - LOG_LEVEL=debug
      - Database configuration guidance

13. Performance and N+1
    - [ ] Eager loading used across list pages (students, teachers, results, fees)
    - [ ] Pagination applied where data is large
    - [ ] No obvious N+1 queries when listing relations

14. Security
    - [ ] Role/permission checks enforced for protected actions
    - [ ] Form Requests sanitize and validate inputs
    - [ ] File uploads constrained (image type and size)

15. Regression Checklist
    - [ ] All CRUD for Departments/Classes/Subjects/Users work as before
    - [ ] Livewire marks entry (if used elsewhere) still behaves correctly
    - [ ] PDF generation (all) works after Pdf facade changes

Notes:
- This checklist targets Phase 1 stabilization changes only.
- Running in local/dev environment is recommended for seeding and PDF verification.
