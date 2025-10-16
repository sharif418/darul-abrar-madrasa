# System Health Dashboard - User Documentation

## Overview

The System Health Dashboard provides administrators with real-time visibility into system integrity, data consistency, and role synchronization status. Access it at `/admin/system-health` (admin-only).

## Features

### 1. Overall Health Score
- Visual health indicator (green/yellow/red)
- Percentage-based health score (0-100%)
- Total issues count
- Health status: Excellent, Good, Warning, Critical

### 2. Data Integrity Monitoring
- Users with missing role records (teacher/student/guardian/accountant)
- Orphaned role records (records without corresponding users)
- Detailed issue lists with user information

### 3. Spatie Role Synchronization
- Users missing Spatie roles
- Users with mismatched roles (legacy vs Spatie)
- Migration progress tracking (percentage of users synced)

### 4. Database Statistics
- Total users by role
- Active vs inactive users
- Role record counts

### 5. Quick Actions
- **Run Verification**: Check for missing role records
- **Sync Roles**: Synchronize Spatie roles with legacy roles
- **Repair Issues**: Automatically create missing role records
- **Export Report**: Download PDF health report

### 6. Activity Monitoring
- Recent system activity logs (last 20 entries)
- Filtered by system events (role changes, repairs, verifications)

## Accessing the Dashboard

**URL:** `/admin/system-health`

**Requirements:**
- Must be logged in as admin
- Admin role required (protected by `role:admin` middleware)

**Navigation:**
- Available in admin sidebar navigation
- Quick action card on admin dashboard
- Direct URL access

## Health Status Indicators

### Excellent (Green - 100%)
- No data integrity issues
- All users have role records
- All Spatie roles synchronized
- System is production-ready

### Good (Green - 95-99%)
- Minor issues affecting < 5% of users
- No critical problems
- System is stable

### Warning (Yellow - 85-94%)
- Moderate issues affecting 5-15% of users
- Requires attention but not urgent
- Recommended to run repairs

### Critical (Red - < 85%)
- Significant issues affecting > 15% of users
- Immediate action required
- Run verification and repair commands

## Using Quick Actions

### Run Verification

**What it does:**
- Checks all users for missing role records
- Reports issues without making changes
- Safe to run anytime

**Equivalent command:**
```bash
php artisan verify:role-records
```

### Sync Spatie Roles

**What it does:**
- Checks Spatie role synchronization
- Repairs mismatches automatically
- Updates model_has_roles table

**Equivalent command:**
```bash
php artisan sync:spatie-roles --repair --force
```

### Repair Issues

**What it does:**
- Creates missing role records with default values
- Generates unique employee IDs and admission numbers
- Creates default departments/classes if needed

**Equivalent command:**
```bash
php artisan verify:role-records --repair --force
```

**Important:**
- Created records have placeholder values
- Update records with actual information after repair

## Exporting Health Reports

### Export PDF

1. Click "Export PDF" button in dashboard header
2. PDF report generates with all health metrics
3. Downloads as `system-health-report-{date}.pdf`
4. Includes:
   - Executive summary
   - All health metrics
   - Issue lists (first 20 items per category)
   - Recommendations
   - Timestamp and admin signature

### Use Cases
- Compliance audits
- System documentation
- Handover reports
- Troubleshooting documentation
- Management reporting

## Best Practices

### Regular Monitoring
- Check health dashboard weekly
- Review after bulk user imports
- Monitor after system updates
- Check before production deployments

### Maintenance Schedule
- **Daily**: Review activity logs for errors
- **Weekly**: Run verification to check for new issues
- **Monthly**: Export health report for records
- **After imports**: Run verification and sync

### Issue Resolution Process

1. **Identify**: Use health dashboard to find issues
2. **Analyze**: Review issue details and affected users
3. **Repair**: Execute repair commands
4. **Verify**: Re-check health dashboard
5. **Update**: Manually update placeholder values in repaired records

## Interpreting Results

### Missing Role Records

**Issue**: User has `role='teacher'` but no record in `teachers` table

**Impact**: User cannot access role-specific features, sees error messages

**Fix**: Run repair to create missing records, then update with actual data

### Orphaned Records

**Issue**: Record in `teachers` table but no corresponding user

**Impact**: Database bloat, potential foreign key issues

**Fix**: Manually review and delete orphaned records

### Missing Spatie Roles

**Issue**: User has legacy role but no Spatie role assigned

**Impact**: Permission checks may fail, features may be inaccessible

**Fix**: Run sync to assign Spatie roles

### Mismatched Roles

**Issue**: User's Spatie role doesn't match legacy role

**Impact**: Inconsistent permissions, unexpected behavior

**Fix**: Run sync with repair to correct mismatches

## Security Considerations

- Dashboard is admin-only (protected by `role:admin` middleware)
- All actions are logged for audit trail
- Repair operations are logged with user ID and timestamp
- Export reports contain sensitive data (handle securely)
- Quick actions require CSRF token
- No public access to health data

## Troubleshooting

### Health Dashboard Not Loading
- Check admin permissions
- Review Laravel logs: `storage/logs/laravel.log`
- Verify database connection
- Clear cache: `php artisan cache:clear`

### Quick Actions Not Working
- Ensure commands are registered: `php artisan list`
- Check command permissions (file system access)
- Review command output in logs
- Verify CSRF token is valid

### Export PDF Fails
- Verify DomPDF is installed: `composer show barryvdh/laravel-dompdf`
- Check storage permissions: `storage/` directory writable
- Review PDF generation logs
- Test with smaller datasets

### High Issue Count
- Review recent system changes
- Check for bulk import errors
- Verify migration scripts ran successfully
- Run verification command manually for detailed output

## Related Features

- **Artisan Commands**: `verify:role-records`, `sync:spatie-roles`
- **Activity Logs**: System event tracking and audit trail
- **Role Management**: User roles and permissions
- **Data Integrity**: Automated verification and repair

## Technical Details

### Routes

- `GET /admin/system-health` - View dashboard
- `GET /admin/system-health/export` - Download PDF report
- `POST /admin/system-health/verify` - Run verification
- `POST /admin/system-health/sync` - Run sync
- `POST /admin/system-health/repair` - Run repair

### Controller Methods

- `DashboardController::systemHealth()` - Main dashboard view
- `DashboardController::exportSystemHealth()` - PDF export
- `DashboardController::runVerification()` - Execute verification
- `DashboardController::runSync()` - Execute sync
- `DashboardController::runRepair()` - Execute repair
- `DashboardController::getSystemHealthData()` - Collect metrics

### Health Score Algorithm

```
Total Issues = Missing Records + Orphaned Records + Missing Spatie + Mismatched Spatie
Health Percentage = 100 - ((Total Issues / Total Users) * 100)

Status Thresholds:
- Excellent: 100%
- Good: 95-99%
- Warning: 85-94%
- Critical: < 85%

Colors:
- Green: Excellent/Good
- Yellow: Warning
- Red: Critical
```

### Database Tables Used

- `users` - User accounts
- `teachers` - Teacher records
- `students` - Student records
- `guardians` - Guardian records
- `accountants` - Accountant records
- `activity_logs` - System activity tracking
- `model_has_roles` - Spatie role assignments

## Support

For technical support or questions about the System Health Dashboard:
- Contact your system administrator
- Review Laravel logs for error details
- Consult the main README.md for general system documentation
- Check SYSTEM_HEALTH_DASHBOARD_IMPLEMENTATION_SUMMARY.md for technical details
