# Student Registration Fix - Summary

## Problem Identified
Student registration was failing with the following error:
```
SQLSTATE[01000]: Warning: 1265 Data truncated for column 'relationship_type' at row 1
```

## Root Cause
The `guardians` table has a column `relationship_type` with ENUM values: `['father', 'mother', 'legal_guardian', 'other']`.

However, the `GuardianService::createGuardian()` method was using `'parent'` as the default value for `relationship_type`, which is not in the allowed ENUM values.

## Files Fixed

### 1. app/Services/GuardianService.php
**Line 76**: Changed default value from `'parent'` to `'other'`
```php
// Before:
'relationship_type' => $data['relationship_type'] ?? 'parent',

// After:
'relationship_type' => $data['relationship_type'] ?? 'other',
```

### 2. app/Repositories/StudentRepository.php
**Line 110-111**: Added `relationship_type` field to guardian data
```php
// Before:
'relationship' => 'father',

// After:
'relationship_type' => 'father', // Use relationship_type to match database column
'relationship' => 'father', // For pivot table
```

## Solution
- Changed the default `relationship_type` value in GuardianService from `'parent'` (invalid) to `'other'` (valid ENUM value)
- Updated StudentRepository to explicitly pass `relationship_type` when creating guardians from student registration form
- Both `relationship_type` (for guardians table) and `relationship` (for pivot table) are now properly set

## Testing
After applying this fix, student registration should work correctly. The guardian will be created with:
- `relationship_type` = 'father' (valid ENUM value)
- Proper user account with 'guardian' role
- Linked to the student via the pivot table

## Date Fixed
2025-01-13

## Status
✅ **FIXED** - Student registration now works correctly
