# Manual Test Checklist - Darul Abrar Madrasa Management System

**Purpose:** Browser-based manual testing checklist for UI/UX validation and end-to-end workflows.

**Test Environment:**
- URL: http://localhost:8000 (or production URL)
- Browser: Chrome/Firefox/Safari
- Test Data: Use seeded data or create test accounts

---

## Pre-Test Setup

**Checklist:**
- [ ] Database is seeded with test data: `php artisan db:seed`
- [ ] Application is running: `php artisan serve`
- [ ] Clear browser cache and cookies
- [ ] Prepare test credentials for all roles

**Test Accounts:**
- Admin: admin@darulabrar.edu / password
- Teacher: teacher@darulabrar.edu / password
- Student: student@darulabrar.edu / password
- Guardian: guardian@darulabrar.edu / password
- Accountant: accountant@darulabrar.edu / password
- Staff: staff@darulabrar.edu / password

---

## 1. Authentication Tests

### 1.1 Login Flow
- [ ] Navigate to /login
- [ ] Verify login form displays correctly
- [ ] Enter valid credentials and click Login
- [ ] Verify redirect to dashboard
- [ ] Verify user name appears in header/navigation
- [ ] Test "Remember Me" checkbox functionality

### 1.2 Login Validation
- [ ] Try login with incorrect password → Verify error message
- [ ] Try login with non-existent email → Verify error message
- [ ] Try login with empty fields → Verify validation errors
- [ ] Verify error messages are user-friendly

### 1.3 Logout Flow
- [ ] Click Logout button/link
- [ ] Verify redirect to login page
- [ ] Try accessing /dashboard → Verify redirect to login
- [ ] Verify session is cleared

### 1.4 Password Reset
- [ ] Click "Forgot Password" link
- [ ] Enter email and submit
- [ ] Verify success message
- [ ] Try with non-existent email → Verify error

---

## 2. Admin Role Tests

**Login as Admin:** admin@darulabrar.edu / password

### 2.1 Dashboard Access
- [ ] Verify admin dashboard loads
- [ ] Verify statistics cards display: Total Students, Teachers, Classes, Departments
- [ ] Verify charts render correctly
- [ ] Verify recent fees table displays
- [ ] Verify upcoming exams list displays

### 2.2 Navigation Visibility
- [ ] Verify sidebar shows admin menu items (Users, Teachers, Students, etc.)
- [ ] Verify no student/teacher/guardian-specific menu items visible

### 2.3 User Management
- [ ] Navigate to Users → Verify users list displays
- [ ] Click "Create User" → Verify form displays
- [ ] Create new user → Verify success message
- [ ] Edit user → Verify changes save correctly

### 2.4 Teacher Management
- [ ] Navigate to Teachers → Verify teachers list displays
- [ ] Create new teacher → Verify success message
- [ ] Edit teacher → Verify changes save

### 2.5 Student Management
- [ ] Navigate to Students → Verify students list displays
- [ ] Create new student → Verify success message
- [ ] Test bulk promote students → Verify students moved to new class

### 2.6 Fee Management
- [ ] Navigate to Fees → Verify fees list displays
- [ ] Create new fee → Verify success message
- [ ] Record payment → Verify status changes
- [ ] Generate invoice → Verify PDF downloads

### 2.7 Reports
- [ ] View Collection Report → Verify data displays
- [ ] View Outstanding Report → Verify pending fees display
- [ ] Test date range filters → Verify results update

### 2.8 Guardian Management
- [ ] Navigate to Guardians → Verify guardians list displays
- [ ] Create new guardian → Verify success message
- [ ] Link guardian to student → Verify relationship created

### 2.9 Accountant Management
- [ ] Navigate to Accountants → Verify accountants list displays
- [ ] Create new accountant → Verify success message
- [ ] Set approval permissions → Verify saves correctly

### 2.10 Notice Management
- [ ] Navigate to Notices → Verify notices list displays
- [ ] Create notice for 'all' → Verify success message
- [ ] Edit notice → Verify changes save
- [ ] Toggle active status → Verify status changes

---

## 3. Teacher Role Tests

**Login as Teacher:** teacher@darulabrar.edu / password

### 3.1 Dashboard Access
- [ ] Verify teacher dashboard loads
- [ ] Verify subjects taught display
- [ ] Verify class information displays
- [ ] Verify charts render correctly

### 3.2 Navigation Visibility
- [ ] Verify sidebar shows teacher menu items only
- [ ] Verify NO admin menu items visible
- [ ] Verify NO student menu items visible

### 3.3 Lesson Plans
- [ ] Navigate to Lesson Plans → Verify only own lesson plans display
- [ ] Create new lesson plan → Verify success message
- [ ] Edit own lesson plan → Verify changes save
- [ ] Mark lesson plan as completed → Verify status updates

### 3.4 Study Materials
- [ ] Navigate to Study Materials → Verify own materials display
- [ ] Create new material → Verify success message
- [ ] Upload document material → Verify file uploads
- [ ] Toggle published status → Verify status changes

### 3.5 Attendance Management
- [ ] Navigate to Attendance → Verify attendance list displays
- [ ] Mark attendance for class → Verify success message
- [ ] View attendance report → Verify data displays correctly

### 3.6 Marks Entry
- [ ] Navigate to Marks Entry → Verify exam selection displays
- [ ] Enter marks for students → Verify validation works
- [ ] Submit marks → Verify success message

### 3.7 Authorization Tests
- [ ] Try to access /users → Verify 403 Forbidden
- [ ] Try to access /fees/create → Verify 403 Forbidden

---

## 4. Student Role Tests

**Login as Student:** student@darulabrar.edu / password

### 4.1 Dashboard Access
- [ ] Verify student dashboard loads
- [ ] Verify attendance percentage displays
- [ ] Verify pending fees amount displays
- [ ] Verify charts render correctly

### 4.2 Navigation Visibility
- [ ] Verify sidebar shows student menu items only
- [ ] Verify NO admin/teacher menu items visible

### 4.3 My Attendance
- [ ] Navigate to My Attendance → Verify own attendance records display
- [ ] Verify attendance percentage calculates correctly
- [ ] Verify date range filter works

### 4.4 My Results
- [ ] Navigate to My Results → Verify own results display
- [ ] Verify results grouped by exam
- [ ] Click "Download Mark Sheet" → Verify PDF downloads

### 4.5 My Fees
- [ ] Navigate to My Fees → Verify own fees display
- [ ] Verify fee status displays correctly
- [ ] Verify total pending amount calculates correctly

### 4.6 Study Materials
- [ ] Navigate to Study Materials → Verify materials for own class display
- [ ] Verify only published materials visible
- [ ] Click "Download" → Verify file downloads

### 4.7 Authorization Tests
- [ ] Try to access /users → Verify 403 Forbidden
- [ ] Try to access /lesson-plans → Verify 403 Forbidden

---

## 5. Guardian Role Tests

**Login as Guardian:** guardian@darulabrar.edu / password

### 5.1 Dashboard Access
- [ ] Verify guardian dashboard loads
- [ ] Verify all linked children display
- [ ] Verify total pending fees displays
- [ ] Verify average attendance displays

### 5.2 My Children
- [ ] Navigate to My Children → Verify all linked children display
- [ ] Click on child → Verify child profile displays
- [ ] Verify cannot see other guardians' children

### 5.3 Child Attendance
- [ ] Click "View Attendance" for child → Verify attendance records display
- [ ] Verify attendance percentage calculates correctly

### 5.4 Child Results
- [ ] Click "View Results" for child → Verify results display
- [ ] Verify exam-wise results display

### 5.5 Child Fees
- [ ] Click "View Fees" for child → Verify fees display
- [ ] Verify pending fees display
- [ ] If NOT financially responsible → Verify appropriate message

### 5.6 Study Materials
- [ ] Navigate to child's study materials → Verify materials display
- [ ] Click "Download" → Verify file downloads

### 5.7 Notices
- [ ] Navigate to Notices → Verify notices for 'guardians' and 'all' display
- [ ] Verify notices for 'students' or 'teachers' NOT visible

### 5.8 Authorization Tests
- [ ] Try to access /users → Verify 403 Forbidden
- [ ] Try to access /accountant/dashboard → Verify 403 Forbidden

---

## 6. Accountant Role Tests

**Login as Accountant:** accountant@darulabrar.edu / password

### 6.1 Dashboard Access
- [ ] Verify accountant dashboard loads
- [ ] Verify financial statistics display
- [ ] Verify charts render correctly

### 6.2 Fee Management
- [ ] Navigate to Fees → Verify all fees display
- [ ] Test filters: Status, Type, Date range
- [ ] Record payment → Verify status updates

### 6.3 Waiver Management
- [ ] Navigate to Waivers → Verify waivers list displays
- [ ] Create new waiver → Verify success message
- [ ] Approve waiver (if has permission) → Verify status changes
- [ ] Reject waiver → Verify rejection reason prompt

### 6.4 Installment Plans
- [ ] Navigate to Installments → Verify fees with installments display
- [ ] Create installment plan → Verify installments created
- [ ] Record payment for installment → Verify status updates

### 6.5 Late Fees
- [ ] Navigate to Late Fees → Verify overdue fees display
- [ ] Apply late fees → Verify confirmation prompt
- [ ] Verify fee amounts updated

### 6.6 Reports
- [ ] View Collection Report → Verify data displays
- [ ] View Outstanding Report → Verify pending fees display
- [ ] Test date range filters → Verify results update

### 6.7 Authorization Tests
- [ ] Try to access /users → Verify 403 Forbidden
- [ ] Try to access /lesson-plans → Verify 403 Forbidden

---

## 7. Staff Role Tests

**Login as Staff:** staff@darulabrar.edu / password

### 7.1 Dashboard Access
- [ ] Verify staff dashboard loads
- [ ] Verify basic statistics display

### 7.2 Limited Access
- [ ] Navigate to Profile → Verify profile page displays
- [ ] Update profile → Verify changes save

### 7.3 Authorization Tests
- [ ] Try to access /users → Verify 403 Forbidden
- [ ] Try to access /lesson-plans → Verify 403 Forbidden
- [ ] Try to access /my-attendance → Verify 403 Forbidden
- [ ] Try to access /guardian/dashboard → Verify 403 Forbidden

---

## 8. Cross-Browser Testing

**Test on Multiple Browsers:**
- [ ] Chrome: All critical paths work
- [ ] Firefox: All critical paths work
- [ ] Safari: All critical paths work
- [ ] Edge: All critical paths work

**Responsive Design:**
- [ ] Desktop (1920x1080): Layout displays correctly
- [ ] Tablet (768x1024): Layout adapts, navigation collapses
- [ ] Mobile (375x667): Layout is mobile-friendly

---

## 9. Performance Testing

- [ ] Dashboard loads within 2 seconds
- [ ] Large lists (100+ items) paginate correctly
- [ ] Charts render without lag
- [ ] File downloads start immediately
- [ ] No console errors in browser developer tools

---

## 10. Accessibility Testing

- [ ] All forms have proper labels
- [ ] Tab navigation works correctly
- [ ] Error messages are clear and visible
- [ ] Color contrast is adequate (WCAG AA)

---

## Test Results Summary

**Date:** _____________
**Tester:** _____________
**Environment:** _____________

**Overall Status:**
- [ ] All tests passed
- [ ] Some tests failed (see Known Issues)
- [ ] Critical issues found (see Known Issues)

**Known Issues:**
1. _____________________________________________
2. _____________________________________________
3. _____________________________________________

**Notes:**
_____________________________________________
_____________________________________________
