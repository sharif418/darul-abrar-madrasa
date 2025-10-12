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

## Phase 1 Stabilization Summary

- Implemented Form Requests for major modules
- Introduced Repository pattern
- Standardized error handling and logging
- Fixed field mismatches (marks_obtained, gpa_point)
- Corrected PDF facade usage (Pdf)
- Integrated FileUploadService in Student/Teacher repositories
- Added routes for mark-sheet and class summary PDFs
- Seeders made idempotent and safe for re-runs
- Added comprehensive testing infrastructure: `docs/MANUAL_TEST_CHECKLIST.md`, `docs/TEST_RESULTS_TEMPLATE.md`, and 98 automated tests

## Troubleshooting

- If PDFs fail to generate:
  - Ensure barryvdh/laravel-dompdf is installed
  - Use Pdf::loadView rather than PDF::loadView
- Seeder re-runs:
  - Safe to re-run RolePermissionSeeder and AdminUserSeeder
  - DemoDataSeeder runs only in local/dev environment
- Permissions:
  - Make sure storage/ is writable if working with file uploads

## New Features (Phase 1 - Guardian & Accountant)

- Guardian Portal with multi-child management (children list, attendance, results, fees, study materials, notices)
- Accountant Portal with advanced financial tools (payments, waivers, installments, late fees, reports, reconciliation)
- Fee Waivers and Scholarships management with approval limits
- Installment Plans for flexible payments
- Automated Late Fee calculation and application (policy-driven)
- Activity Logging for audit trail (actions on fees, waivers, late fees, installments)
- Enhanced Authorization with Policies (Fees, Study Materials, Waivers, etc.)
- Secure Study Material downloads via policy checks

## Role-Based Access

- Admin
  - Full access to all modules; manages roles/permissions
- Teacher
  - Class and subject operations; results, attendance, study materials
- Student
  - Own results/attendance/fees; study materials; notices
- Guardian
  - Guardian dashboard; view children’s info, attendance, results, fees; pay fees online; notices
- Accountant
  - Fees, payments, waivers, installments, late fees, reports, reconciliation; audit logs
- Staff (optional)
  - Read-only access to foundational modules as configured

Permissions are enforced via Spatie/laravel-permission with temporary dual-check fallback to legacy string role column during migration.

## Setup Instructions (Additions for Guardian/Accountant Features)

1. Dependencies
   - Composer packages already configured:
     - spatie/laravel-permission
     - barryvdh/laravel-dompdf
     - livewire/livewire
2. Publish and migrate permissions (if not already done)
   - php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
   - php artisan migrate
3. Seed roles and permissions
   - php artisan db:seed --class=RolePermissionSeeder
4. Storage link (if not yet configured)
   - php artisan storage:link
5. Scheduler (cron) setup
   - Add to crontab:
     - * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1

## Scheduled Tasks

- fees:apply-late-fees — daily (applies late fees based on LateFeePolicy)
- fees:send-reminders — daily at 09:00 (sends reminders to guardians)
- Verify tasks:
  - php artisan schedule:list

Example crontab entry:
- * * * * * cd /var/www/darul-abrar-madrasa && php artisan schedule:run >> /dev/null 2>&1

## Data Integrity Verification

### Command: verify:role-records

Verifies data integrity between the `users` table and role-specific tables (teachers, students, guardians, accountants). This command helps identify and fix missing role records that can cause null pointer exceptions.

**Usage:**

```bash
# Verify all role records (read-only)
php artisan verify:role-records

# Verify specific role only
php artisan verify:role-records --role=teacher
php artisan verify:role-records --role=student

# Preview what would be repaired (dry run)
php artisan verify:role-records --repair --dry-run

# Repair missing records (creates records with default values)
php artisan verify:role-records --repair

# Repair without confirmation prompt
php artisan verify:role-records --repair --force

# Repair specific role only
php artisan verify:role-records --repair --role=guardian
```

**Options:**
- `--repair` - Create missing role records with default values
- `--role=ROLE` - Verify only specific role (teacher, student, guardian, accountant)
- `--dry-run` - Show what would be repaired without making changes
- `--force` - Skip confirmation prompts

**When to Use:**
- After importing users from external systems
- After database migrations or data restoration
- When users report "profile incomplete" errors
- As part of regular system health checks
- Before deploying to production

**Important Notes:**
- Created records will have placeholder values (e.g., "To be updated")
- After repair, manually update records with actual information
- Teachers are assigned to a default department if none exists
- Students are assigned to a default class if none exists
- Employee IDs and admission numbers are auto-generated with unique timestamps
- All repair operations are wrapped in database transactions
- Failed repairs are logged and don't affect other records

**Example Output:**

```
Verifying role records...

========================================
Verifying teacher records...
Found 15 users with role 'teacher'
⚠ Found 2 missing teacher records

+----+------------------+----------------------+---------------------+
| ID | Name             | Email                | Created At          |
+----+------------------+----------------------+---------------------+
| 45 | John Doe         | john@example.com     | 2025-01-10 10:30:00 |
| 67 | Jane Smith       | jane@example.com     | 2025-01-11 14:20:00 |
+----+------------------+----------------------+---------------------+

========================================
VERIFICATION SUMMARY
========================================
teacher: 2 missing
student: 0 missing
guardian: 1 missing
accountant: 0 missing

Total missing records: 3

Run with --repair flag to create missing records
Run with --repair --dry-run to preview changes
```

**Logging:**
- All verification and repair actions are logged to `storage/logs/laravel.log`
- Search for "VerifyRoleRecords" in logs for audit trail
- Each created record is logged with user ID and generated identifiers

**Related Commands:**
- `php artisan db:seed --class=RolePermissionSeeder` - Seed roles and permissions
- `php artisan sync:spatie-roles` - Synchronize Spatie roles with legacy role column

**Troubleshooting:**
- If repair fails due to missing departments, the command will create a default "General" department
- If repair fails due to missing classes, the command will create a default "General Class"
- Check logs for detailed error messages if repair fails
- Ensure database has proper permissions for INSERT operations
- Run with `--dry-run` first to preview changes before applying

### Command: sync:spatie-roles

Synchronizes Spatie permission roles with the legacy `users.role` column. This command ensures consistency between the two role systems during the migration period.

**Purpose:**
- Identifies users with missing Spatie roles
- Detects mismatched roles (Spatie role doesn't match legacy role)
- Finds users with multiple Spatie roles when they should have one
- Syncs Spatie roles to match the legacy role column

**Usage:**

```bash
# Verify role synchronization (read-only)
php artisan sync:spatie-roles

# Verify specific role only
php artisan sync:spatie-roles --role=teacher
php artisan sync:spatie-roles --role=guardian

# Preview what would be synced (dry run)
php artisan sync:spatie-roles --repair --dry-run

# Sync roles (creates/updates Spatie role assignments)
php artisan sync:spatie-roles --repair

# Sync without confirmation prompt
php artisan sync:spatie-roles --repair --force

# Sync specific role only
php artisan sync:spatie-roles --repair --role=student
```

**Options:**
- `--repair` - Sync Spatie roles to match legacy role column
- `--role=ROLE` - Sync only specific role (admin, teacher, student, staff, guardian, accountant)
- `--dry-run` - Show what would be changed without making changes
- `--force` - Skip confirmation prompts

**When to Use:**
- After creating users manually (bypassing proper role assignment)
- After importing users from external systems
- After direct database updates to the `users.role` column
- When users report permission errors despite having correct legacy role
- As part of migration from legacy role system to Spatie permissions
- Before deploying to production (verify all roles are synced)
- After running database migrations or restorations

**What It Detects:**

1. **Missing Spatie Roles:**
   - User has `role='teacher'` in users table
   - User has no Spatie role assigned
   - Action: Assigns 'teacher' Spatie role

2. **Mismatched Roles:**
   - User has `role='teacher'` in users table
   - User has 'student' Spatie role assigned
   - Action: Removes 'student', assigns 'teacher'

3. **Multiple Spatie Roles:**
   - User has `role='teacher'` in users table
   - User has both 'teacher' and 'admin' Spatie roles
   - Action: Removes all roles, assigns only 'teacher'

4. **Synced (No Issues):**
   - User has `role='teacher'` in users table
   - User has exactly 'teacher' Spatie role
   - Action: No changes needed

**Example Output:**

```
==========================================================
SPATIE ROLES SYNCHRONIZATION
==========================================================
Mode: VERIFICATION ONLY
Timestamp: 2025-01-11 10:30:00

============================================================
Verifying teacher role synchronization...
Found 15 users with legacy role 'teacher'
⚠ Found 3 users missing Spatie role

+----+------------------+----------------------+--------------+
| ID | Name             | Email                | Legacy Role  |
+----+------------------+----------------------+--------------+
| 45 | John Doe         | john@example.com     | teacher      |
| 67 | Jane Smith       | jane@example.com     | teacher      |
| 89 | Bob Johnson      | bob@example.com      | teacher      |
+----+------------------+----------------------+--------------+

⚠ Found 1 user with mismatched Spatie role

+----+------------------+--------------------+---------------+
| ID | Name             | Current Spatie     | Expected Role |
+----+------------------+--------------------+---------------+
| 23 | Alice Brown      | student            | teacher       |
+----+------------------+--------------------+---------------+

============================================================
SYNCHRONIZATION SUMMARY
============================================================
Admin: 0 missing, 0 mismatched, 0 extra
Teacher: 3 missing, 1 mismatched, 0 extra
Student: 0 missing, 0 mismatched, 0 extra
Staff: 0 missing, 0 mismatched, 0 extra
Guardian: 1 missing, 0 mismatched, 0 extra
Accountant: 0 missing, 0 mismatched, 0 extra

Total issues found: 5
Migration Progress: 85% (170/200 users have Spatie roles)

Run with --repair flag to sync roles
Run with --repair --dry-run to preview changes
```

**Important Notes:**
- The legacy `users.role` column is the source of truth
- Sync operation uses `syncRoles()` which replaces all existing Spatie roles
- Users with multiple Spatie roles will have all but the correct one removed
- All sync operations are wrapped in database transactions
- Failed syncs are logged and don't affect other users
- Safe to run multiple times (idempotent)
- Does not modify the legacy `users.role` column

**Migration Strategy:**

1. **Initial Sync:**
   ```bash
   # Verify current state
   php artisan sync:spatie-roles
   
   # Preview changes
   php artisan sync:spatie-roles --repair --dry-run
   
   # Apply sync
   php artisan sync:spatie-roles --repair
   ```

2. **Verify Data Integrity:**
   ```bash
   # Check role records exist
   php artisan verify:role-records
   
   # Check Spatie roles synced
   php artisan sync:spatie-roles
   ```

3. **Monitor Drift:**
   - Run verification weekly (can be scheduled)
   - Review logs for inconsistencies
   - Repair as needed

4. **Complete Migration:**
   - Once all users have Spatie roles (100% progress)
   - Update code to use only Spatie role checks
   - Remove legacy role fallbacks from `User::hasRole()` and `CheckRole` middleware
   - Consider deprecating the `users.role` column (keep for backup)

**Logging:**
- All verification and sync actions are logged to `storage/logs/laravel.log`
- Search for "SyncSpatieRoles" in logs for audit trail
- Each sync operation is logged with before/after state
- Errors are logged with full context

**Related Commands:**
- `php artisan verify:role-records` - Verify role-specific table records exist
- `php artisan db:seed --class=RolePermissionSeeder` - Seed roles and permissions
- `php artisan permission:cache-reset` - Clear Spatie permission cache

**Troubleshooting:**

**Issue: "Role not found" error**
- Solution: Run `php artisan db:seed --class=RolePermissionSeeder` to create roles
- Or: The command will auto-create missing roles

**Issue: Sync fails for some users**
- Check logs for specific error messages
- Verify database permissions for INSERT/UPDATE operations
- Ensure `model_has_roles` table exists
- Run with `--dry-run` to identify problematic users

**Issue: Migration progress stuck below 100%**
- Run verification to identify users without Spatie roles
- Check if users have invalid legacy roles
- Verify all role records exist: `php artisan verify:role-records`

**Issue: Users still using legacy role checks**
- Check `CheckRole` middleware logs for legacy fallback warnings
- Review application code for direct `$user->role` checks
- Update code to use `$user->hasRole()` or `@role()` directives

## System Health Dashboard

The System Health Dashboard provides administrators with real-time visibility into system integrity, data consistency, and role synchronization status.

**Access:** `/admin/system-health` (admin-only)

**Features:**
- Overall health score (0-100%) with color-coded status
- Data integrity monitoring (missing/orphaned role records)
- Spatie role synchronization tracking
- Database statistics by role
- Quick actions (verify, sync, repair)
- PDF export for compliance and auditing

**Documentation:** See `SYSTEM_HEALTH_DASHBOARD_DOCUMENTATION.md` for complete usage guide.

**Quick Start:**
```bash
# Access dashboard
Visit: /admin/system-health (admin login required)

# Run verification
Click "Run Verification" button or: php artisan verify:role-records

# Export report
Click "Export PDF" button
```

## Testing

### Overview

The Darul Abrar Madrasa Management System includes comprehensive test coverage for all user roles and critical functionality. Tests are organized into Feature tests (end-to-end workflows) and Unit tests (isolated component testing).

### Test Structure

```
tests/
├── Feature/
│   ├── AdminDashboardTest.php          # Admin role tests (12 tests)
│   ├── TeacherDashboardTest.php        # Teacher role tests (13 tests)
│   ├── StudentDashboardTest.php        # Student role tests (15 tests)
│   ├── GuardianPortalTest.php          # Guardian role tests (7 tests)
│   ├── AccountantPortalTest.php        # Accountant role tests (9 tests)
│   ├── StaffDashboardTest.php          # Staff role tests (10 tests)
│   ├── AuthenticationFlowTest.php      # Login/logout tests (17 tests)
│   ├── RoleAuthorizationTest.php       # Cross-role authorization (12 tests)
│   └── NullPointerScenariosTest.php    # Missing role records (19 tests)
├── Unit/
│   └── FeeRepositoryTest.php           # Fee business logic tests
└── TestCase.php                         # Base test class
```

### Running Tests

**Run all tests:**
```bash
php artisan test
```

**Run specific test suite:**
```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

**Run specific test file:**
```bash
php artisan test tests/Feature/AdminDashboardTest.php
php artisan test tests/Feature/GuardianPortalTest.php
```

**Run specific test method:**
```bash
php artisan test --filter test_admin_can_access_dashboard
php artisan test --filter test_guardian_can_view_children_list
```

**Run tests with coverage:**
```bash
php artisan test --coverage
php artisan test --coverage-html coverage-report
```

**Run tests in parallel (faster):**
```bash
php artisan test --parallel
```

### Test Coverage

**Role-Based Tests:**
- ✅ Admin: User management, fee management, reports, bulk operations
- ✅ Teacher: Lesson plans, study materials, attendance, marks entry
- ✅ Student: View own attendance, results, fees, study materials
- ✅ Guardian: View children's data, fees, attendance, results
- ✅ Accountant: Fee payments, waivers, installments, late fees, reports
- ✅ Staff: Minimal access (dashboard, profile)

**Authentication Tests:**
- ✅ Login with valid/invalid credentials
- ✅ Logout functionality
- ✅ Password reset flow
- ✅ Remember me functionality
- ✅ Role-based redirects after login

**Authorization Tests:**
- ✅ Role boundaries (403 for unauthorized access)
- ✅ Data isolation (users can only see their own data)
- ✅ Cross-role authorization
- ✅ CheckRole middleware (Spatie + legacy dual-check)

**Edge Cases:**
- ✅ Null pointer scenarios (missing role records)
- ✅ Error handling and logging
- ✅ User-friendly error messages

### Manual Testing

For browser-based UI/UX testing, use the comprehensive manual test checklist:

**Location:** `docs/MANUAL_TEST_CHECKLIST.md`

**Covers:**
- Authentication flows
- Dashboard functionality for all roles
- Navigation visibility
- Feature access and authorization
- Cross-browser compatibility
- Responsive design
- Performance and accessibility

**How to Use:**
1. Set up test environment with seeded data
2. Follow checklist step-by-step for each role
3. Mark items as passed/failed
4. Document issues in test results template

### Test Results Documentation

Document test results using the provided template:

**Location:** `docs/TEST_RESULTS_TEMPLATE.md`

**Includes:**
- Executive summary
- Automated test results
- Manual test results
- Known issues with severity and status
- Performance metrics
- Security testing results
- Recommendations and sign-off

### Test Data Setup

**Seed database with test data:**
```bash
php artisan db:seed
```

**Seed specific seeder:**
```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=DemoDataSeeder
```

**Reset database and seed:**
```bash
php artisan migrate:fresh --seed
```

**Test user credentials (after seeding):**
- Admin: admin@darulabrar.edu / password
- Teacher: teacher@darulabrar.edu / password
- Student: student@darulabrar.edu / password
- Guardian: guardian@darulabrar.edu / password
- Accountant: accountant@darulabrar.edu / password
- Staff: staff@darulabrar.edu / password

### Best Practices

1. **Run tests before committing:** Ensure all tests pass before pushing code
2. **Write tests for new features:** Every new feature should have corresponding tests
3. **Test edge cases:** Don't just test happy paths
4. **Keep tests isolated:** Each test should be independent
5. **Use descriptive test names:** Test names should clearly describe what they test
6. **Mock external services:** Don't rely on external APIs in tests
7. **Test authorization:** Always test that unauthorized users get 403
8. **Test data isolation:** Verify users can only see their own data

### Related Commands

- `php artisan verify:role-records` - Verify role record integrity
- `php artisan sync:spatie-roles` - Sync Spatie roles with legacy roles
- `php artisan db:seed` - Seed test data
- `php artisan migrate:fresh --seed` - Reset and seed database

## Configuration (.env example keys)

Payment Gateways:
- PAYMENT_GATEWAY=sslcommerz
- SSLCOMMERZ_STORE_ID=
- SSLCOMMERZ_STORE_PASSWORD=
- SSLCOMMERZ_SANDBOX=true
- BKASH_APP_KEY= / BKASH_APP_SECRET= / BKASH_USERNAME= / BKASH_PASSWORD= / BKASH_SANDBOX=true
- NAGAD_MERCHANT_ID= / NAGAD_MERCHANT_NUMBER= / NAGAD_PUBLIC_KEY= / NAGAD_PRIVATE_KEY= / NAGAD_SANDBOX=true

SMS Gateway:
- SMS_GATEWAY=
- SMS_API_KEY=
- SMS_SENDER_ID=
- SMS_API_URL=

Email:
- MAIL_MAILER=smtp
- MAIL_HOST=
- MAIL_PORT=
- MAIL_USERNAME=
- MAIL_PASSWORD=
- MAIL_ENCRYPTION=
- MAIL_FROM_ADDRESS=
- MAIL_FROM_NAME="${APP_NAME}"

Application Settings:
- INVOICE_PREFIX=INV
- LATE_FEE_AUTO_APPLY=false
- FEE_REMINDER_DAYS=7
- GRACE_PERIOD_DAYS=3

Activity Logging:
- ACTIVITY_LOG_ENABLED=true
- ACTIVITY_LOG_RETENTION_DAYS=365

## Security

- Authorization Policies:
  - FeePolicy, StudyMaterialPolicy, FeeWaiverPolicy (granular control for view, create, update, delete, download, approve, etc.)
- Activity Logging:
  - Centralized ActivityLogService for critical actions (payments, waivers approvals, late fees, etc.)
- Secure File Downloads:
  - Study materials served via Storage::download with policy checks
- CSRF and Standard Laravel Security Middleware enabled

## Future Roadmap

- Phase 2: Timetable and Assignments; enhanced Study Materials
- Phase 3: Library, Hostel, Transport modules
- Phase 4: Communications Hub, Admissions, Advanced Analytics/Reporting

## Contributing

- Follow Laravel coding standards (Pint)
- Write tests for new features (php artisan test)
- Use repositories for business logic and Form Requests for validation
- Submit PRs with clear descriptions and test coverage

## License

This project is open-sourced under the MIT license.
