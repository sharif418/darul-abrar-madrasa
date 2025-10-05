# Darul Abrar Madrasa Management System

A Laravel 12-based madrasa management system for managing students, teachers, classes, subjects, exams, results, fees, attendance, notices, and more.

Key technologies:
- PHP 8.2, Laravel 12
- Spatie Permissions for RBAC
- Barryvdh DomPDF for PDF generation
- Livewire 3.x (marks entry module)
- Repository + Form Request validation patterns

## Features

- Authentication and role-based access (admin, teacher, student, staff)
- Departments, classes, subjects management
- Students and teachers CRUD with avatar uploads
- Exams and results (bulk entry, statistics, mark-sheet PDF, class summary PDF)
- Attendance (bulk entry by class, student view)
- Fees (create, update, record payments, invoices, collection and outstanding reports)
- Notices (public and role-targeted)
- Seeders for demo data and role/permission setup
- Logging and robust error handling in controllers

## Requirements

- PHP >= 8.2
- Composer
- Database (SQLite/MySQL/PostgreSQL) and configured PHP extensions
- Node.js (optional, if you build frontend assets)

## Installation

1. Clone repository
   - git clone <repo-url>
   - cd darul-abrar-madrasa

2. Environment setup
   - cp .env.example .env
   - Configure database connection in .env
   - Set APP_NAME="Darul Abrar Madrasa"
   - Recommended: LOG_CHANNEL=stack and LOG_LEVEL=debug for local

3. Install dependencies
   - composer install
   - php artisan key:generate

4. Migrate and seed
   - php artisan migrate --seed
   - This runs:
     - RolePermissionSeeder (idempotent)
     - AdminUserSeeder (idempotent, skips in production)
     - DemoDataSeeder (only in local/dev)

5. Storage (if needed)
   - php artisan storage:link

6. Serve
   - php artisan serve
   - Open http://localhost:8000

## Default Credentials

- Admin
  - Email: admin@darulabrar.com
  - Password: Admin@2025

## Project Structure Highlights

- app/Http/Controllers
  - Thin controllers with try/catch, logging, and HTTP concerns
- app/Http/Requests
  - Form Request validation for all major modules (Students, Teachers, Fees, Attendance, Exams, Results, Departments, Classes, Subjects, Users, Notices)
- app/Repositories
  - Data access and business logic abstraction (Students, Teachers, Fees, Attendance, Exams, Results)
- app/Services
  - FileUploadService centralizes file upload/delete logic
- app/Models
  - Rich models with scopes, accessors, and domain methods (e.g., Result::calculateGradeAndGpa)

## Results Module (Key Endpoints)

- Bulk entry form:
  - GET /results/create/{exam_id}/{class_id}/{subject_id}
- Store bulk results:
  - POST /results/store-bulk
- Student mark-sheet PDF:
  - GET /results/{exam}/{student}/mark-sheet
- Class result summary PDF:
  - GET /results/{exam}/class-summary/pdf

Note: Results use marks_obtained and gpa_point everywhere. Grade/GPA are computed and persisted via Result::calculateGradeAndGpa().

## Fees Module

- Invoice PDF:
  - GET /fees/{fee}/invoice
- Record payment:
  - POST /fees/{fee}/record-payment
  - Payload: paid_amount, payment_method, transaction_id (optional), remarks (optional)
- Reports:
  - Collection report: /fees-reports/collection
  - Outstanding report (with overdue filter): /fees-reports/outstanding

## Seeders

- RolePermissionSeeder
  - Idempotent via firstOrCreate + syncPermissions
- AdminUserSeeder
  - Idempotent via updateOrCreate; warns and skips in production
- DemoDataSeeder
  - Dev-only seed for departments, classes, subjects, teachers, students, exams, fees, attendance, notices
  - Results created with marks_obtained then grade/gpa_point computed by model

## Testing

A comprehensive manual testing plan is documented here:
- PHASE1_TESTING_CHECKLIST.md

Focus areas:
- Result entry (marks_obtained), GPA calculation, PDFs
- Attendance bulk entry (studentId-keyed map)
- Fees payment recording (payment_method), overdue filters, invoice PDFs
- Routes and access control for all roles
- Form Request validations for all modules

## Phase 1 Stabilization Summary

- Implemented Form Requests for major modules
- Introduced Repository pattern
- Standardized error handling and logging
- Fixed field mismatches (marks_obtained, gpa_point)
- Corrected PDF facade usage (Pdf)
- Integrated FileUploadService in Student/Teacher repositories
- Added routes for mark-sheet and class summary PDFs
- Seeders made idempotent and safe for re-runs
- Created PHASE1_TESTING_CHECKLIST.md

## Troubleshooting

- If PDFs fail to generate:
  - Ensure barryvdh/laravel-dompdf is installed
  - Use Pdf::loadView rather than PDF::loadView
- Seeder re-runs:
  - Safe to re-run RolePermissionSeeder and AdminUserSeeder
  - DemoDataSeeder runs only in local/dev environment
- Permissions:
  - Make sure storage/ is writable if working with file uploads

## License

This project is open-sourced under the MIT license.
