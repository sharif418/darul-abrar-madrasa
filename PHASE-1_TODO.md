# Phase 1 — Integrity &amp; Completion Audit: TODO and Execution Tracker

Scope approved: Thorough testing (complete coverage) with automation + manual interactive testing.

This document tracks concrete tasks derived from the “Darul Abrar Rebuild &amp; Expansion Plan” Part 1 (sections 1.1–1.7), including testing scope, fixes, and acceptance checks.

---

## 0) Test Plan and Execution

- [x] Confirm testing scope with stakeholder (Thorough testing)
- [ ] Prepare smoke and regression runs:
  - [ ] Run automation scripts:
    - [ ] ./darul-abrar-madrasa/full_phase1_health_check.sh
    - [ ] ./darul-abrar-madrasa/comprehensive_module_testing.sh
    - [ ] ./darul-abrar-madrasa/verify_deployment.sh
    - [ ] ./darul-abrar-madrasa/test_login_dashboard.sh
    - [ ] ./darul-abrar-madrasa/test_users_module.sh
  - [ ] Capture outputs and summarize findings (outputs/*.txt)
- [ ] Manual, interactive coverage (cross-role):
  - [ ] Authentication: login/logout, redirects, proxy/419
  - [ ] Dashboards: admin/teacher/student widgets and stats
  - [ ] Students: CRUD, enroll/transfer/promote, bulk actions
  - [ ] Teachers, Departments, Classes: CRUD, assign-subject, enroll/unenroll, unassign
  - [ ] Subjects: CRUD
  - [ ] Exams &amp; Results: create/edit, bulk marks entry, publish gating, PDFs and mark sheets
  - [ ] Fees: create, payment, receipts, reports (collection/outstanding), filters, exports, invoice visibility
  - [ ] Notices: admin CRUD, public page behavior
  - [ ] Study Materials: upload/preview/publish toggle, “my materials” (student), secure downloads
  - [ ] UI polish items: modals, toasts, loading overlays, empty states, sorting/filtering, responsiveness, A11y
- [ ] Summarize “Findings &amp; Fixes” report with prioritized action items

---

## 1) Bugs, Risks, and Inconsistencies (1.1)

1.1.1 Public Notices route (routing/auth boundary)
- Current: /notices/public is inside auth group (routes/web.php).
- Tasks:
  - [ ] Move /notices/public route out of auth middleware to be truly public.
  - [ ] Retain Route::pattern('notice', '[0-9]+') to avoid conflicts.
  - [ ] Verify that private/internal notices remain protected.
  - [ ] Add feature test for access without auth.
- Acceptance:
  - [ ] Unauthenticated users can view public notices page.
  - [ ] No conflicts with resource routes and numeric binding.

1.1.2 Root landing behavior
- Current: Route / returns default Laravel welcome page.
- Tasks:
  - [ ] Introduce redirect behavior:
    - [ ] Guest → login
    - [ ] Authenticated → /dashboard
  - [ ] Optional: Keep welcome for development only.
- Acceptance:
  - [ ] Clean entry flow without dead-ends.

1.1.3 Role middleware consistency
- Current: Routes use role:admin, role:admin,teacher; confirm provider.
- Tasks:
  - [ ] Identify and confirm role middleware implementation (custom vs package).
  - [ ] If custom: verify logic and registration; add tests.
  - [ ] Extract or standardize policies for granular authorization (defense in depth).
- Acceptance:
  - [ ] Role checks consistent; policy checks on critical models.

1.1.4 CSRF and HTTP verbs on destructive actions
- Tasks:
  - [ ] Ensure all delete/unassign/unenroll actions use form POST with method DELETE, CSRF present.
  - [ ] Audit related blades (classes, fees, results, notices).
- Acceptance:
  - [ ] No GET-based state-changing endpoints.

1.1.5 Study Material download security
- Tasks:
  - [ ] Audit StudyMaterialController@download for authorization:
    - [ ] Student: only if enrolled in matching class/subject
    - [ ] Teacher/Admin: domain-based access
  - [ ] Validate storage access, MIME sniffing, and proper response download flow.
  - [ ] Consider signed route or hashed IDs if needed.
- Acceptance:
  - [ ] Access restricted and secure; no path traversal; correct headers.

1.1.6 TrustProxies/session and 419
- Tasks:
  - [ ] Validate APP_URL, SESSION_SECURE_COOKIE, and proxy headers in production.
  - [ ] Confirm CSRF cookie issuance and verification across POST flows.
- Acceptance:
  - [ ] Stable login and POST flows without 419 under proxy.

1.1.7 Data integrity (FKs and indices)
- Tasks:
  - [ ] Re-audit schema for attendances/results/enrollment/assignments FKs.
  - [ ] Add missing indices for FK columns if necessary.
  - [ ] Run integrity checks and fix any cascading rules.
- Acceptance:
  - [ ] Migrate refresh in staging passes; no FK errors.

1.1.8 Fee/Payment references
- Tasks:
  - [ ] Review FeeController (recordPayment, invoices) to confirm no stale references to removed Payment model/service.
- Acceptance:
  - [ ] Fees flows error-free; consistent receipts and reports.

---

## 2) Incomplete Features &amp; Placeholders (1.2)

2.1 Attendance “my-attendance” chart
- Tasks:
  - [ ] Replace placeholder with real chart (progress over time, monthly heatmap).
  - [ ] Keep graceful fallback without JS.
- Acceptance:
  - [ ] Visual insight present for students; no JS console errors.

2.2 Study Materials UX
- Tasks:
  - [ ] Finalize create/edit (content type validation, filters by class/subject).
  - [ ] Preview and metadata display (size, type, publish state).
  - [ ] Bulk upload optional (phase later).
- Acceptance:
  - [ ] Smooth teacher flow; “my materials” listing coherent for students.

2.3 Dashboards completion
- Tasks:
  - [ ] Ensure stat cards/widgets have real data:
    - [ ] Fees due/recovered
    - [ ] Exams upcoming/published
    - [ ] Attendance anomalies
  - [ ] Add links to detailed pages.
- Acceptance:
  - [ ] Dashboards feel complete and useful.

2.4 Fees reporting completeness
- Tasks:
  - [ ] Add date/class/section/guardian filters.
  - [ ] Implement CSV/PDF export.
  - [ ] Reconcile totals with ledger (once ledger added).
- Acceptance:
  - [ ] Accurate totals, useful exports.

---

## 3) Logical Gaps in Workflows (1.3)

3.1 Admissions funnel
- Tasks:
  - [ ] Plan admissions module (applicant → admitted → enrolled).
  - [ ] Interim: document manual process or placeholders.
- Acceptance:
  - [ ] Clear path documented; story tickets created.

3.2 Guardian/Parent linkage
- Tasks:
  - [ ] Design guardian model &amp; relations (multi-children, contact prefs).
  - [ ] Prepare migrations and policy docs (no code yet).
- Acceptance:
  - [ ] Data model design approved (for Phase 2).

3.3 Financial lifecycle
- Tasks:
  - [ ] Define waivers/discounts/installments/late fee policies.
  - [ ] Prepare domain model and UX flows.
- Acceptance:
  - [ ] Ready for accountant role implementation.

3.4 Academic lifecycle
- Tasks:
  - [ ] Define Timetable/Routine model (periods/rooms/conflicts).
  - [ ] Assignments/homework flows.
- Acceptance:
  - [ ] Designs ready for Phase 2.

3.5 Communications Hub
- Tasks:
  - [ ] Template engine, audience builder, logs, preferences—high-level.
- Acceptance:
  - [ ] Epics ready for Phase 2.

3.6 Multi-campus readiness
- Tasks:
  - [ ] Schema dimensioning plan; tenant strategy (single-tenant now).
- Acceptance:
  - [ ] Backlog ready; no breaking changes in Phase 1.

---

## 4) Security, Privacy, Compliance (1.4)

- [ ] Standardize RBAC provider (confirm custom role middleware or adopt spatie/permission later)
- [ ] Add Policies coverage and tests (Users, Students, Fees, Results, StudyMaterials, Notices, etc.)
- [ ] Activity logs (plan: spatie/laravel-activitylog) for critical actions
- [ ] PII safeguards: masking, encryption (where needed), export rules
- [ ] Backups &amp; DR: document procedures, scheduled backups, retention policy

---

## 5) Performance &amp; Scalability (1.5)

- [ ] Query optimization; add with() to avoid N+1
- [ ] Cache strategy for heavy dashboard/report queries
- [ ] Asset split/lazy load where beneficial; image/PDF compression guidance
- [ ] Index review on commonly filtered columns

---

## 6) DevEx, CI/CD, Testing (1.6)

- [ ] GitHub Actions: CI pipeline (lint, unit, feature, build)
- [ ] Environment separation; .env.example completeness
- [ ] Tests:
  - [ ] Unit tests for services/policies
  - [ ] Feature tests for critical flows (auth, enroll, fees, results, notices)
  - [ ] Browser tests plan (Dusk/Panther) for key pages
- [ ] Observability: Sentry/APM plan, uptime monitor

---

## 7) UX/UI Polish (1.7)

- [ ] Standard modal component usage (confirm-delete/unassign/unenroll)
- [ ] Unified toast messages; success/error patterns
- [ ] Loading overlays and skeletons on heavy pages
- [ ] Helpful empty states with CTAs
- [ ] Tables: persistent filters, sorting, sticky headers, per-page sizing, export
- [ ] Forms: validation visuals, helper text, autofocus, required markers
- [ ] Responsive checks; A11y (contrast, focus, ARIA)
- [ ] Navigation: active state; breadcrumbs for deep pages

---

## Execution Order (Initial High-Impact Items)

1) [Routing] Make /notices/public truly public (outside auth middleware)
2) [Security] Harden study material download authorization and file handling
3) [AuthZ] Confirm role middleware implementation and add/enforce model policies
4) [CSRF/HTTP] Audit all destructive actions for correct verbs &amp; CSRF
5) [Integrity] Re-validate FKs and indexes (attendances/results/enrollment/assignments)
6) [UX polish] Modals, toasts, loading overlays, empty states; fix obvious inconsistencies

---

## Deliverables

- [ ] Updated routes/web.php for public notices
- [ ] Updated StudyMaterialController (download auth, validation)
- [ ] Policies scaffold and applied to critical models
- [ ] Blade fixes for CSRF/HTTP verbs on destructive actions
- [ ] Integrity report + FK/index adjustments
- [ ] Findings &amp; Fixes report (linked artifacts and test evidence)

---

## Notes

- No Phase 2 (expansion) implementation in Phase 1; only designs and readiness steps where needed.
- All changes to be covered by tests (unit/feature) where applicable.
- Keep changes atomic and documented in commit messages referencing this TODO.
