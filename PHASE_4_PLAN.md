# Phase 4: Admin Dashboard & Reporting - Implementation Plan

## Overview

Phase 4 focuses on completing the admin panel with user management, complaint management, reporting, and system administration features. This phase builds on the solid foundation of Phases 1-3 and adds the administrative layer needed for system oversight.

**Status:** In Progress (60% Complete)
**Estimated Completion:** Based on prioritization

---

## ✅ Completed Features (60%)

### 1. Admin Dashboard (✅ COMPLETE)
- **File:** `resources/views/admin/dashboard.blade.php`
- **Controller:** `app/Http/Controllers/Admin/WorkspaceController.php::dashboard()`
- **Features:**
  - Dashboard statistics (total complaints, resolved, pending, rejected, in-progress)
  - Recent complaints with status indicators
  - Staff member performance overview
  - System notifications

### 2. Admin Routes & Middleware (✅ COMPLETE)
- **File:** `routes/web.php`
- **Features:**
  - Admin-only middleware guard
  - Routes for all admin operations
  - Proper authorization through policies

### 3. Database Infrastructure (✅ COMPLETE)
- **Files:** `database/migrations/`
- **Tables:**
  - `users` (with role field)
  - `complaints`
  - `complaint_activities`
  - `complaint_evidences`
  - `barangay_information`
  - All support tables for caching, jobs, etc.

### 4. Admin Policies (✅ COMPLETE)
- **File:** `app/Policies/UserPolicy.php`
- **File:** `app/Policies/ComplaintPolicy.php`
- **Features:**
  - viewAny, view, create, update, delete for users
  - Admin-only authorization

---

## 🔄 In Progress (40%)

### 1. User Management Interface
**Priority:** HIGH
**Status:** 50% Complete

#### Views Created
- `resources/views/admin/users.blade.php` - User listing page

#### Views Needed
- `resources/views/admin/users/show.blade.php` - User detail view
- `resources/views/admin/users/create.blade.php` - Create staff form
- `resources/views/admin/users/edit.blade.php` - Edit staff form

#### Controllers Needed
- Create `app/Http/Controllers/Admin/UserController.php`
  - Methods: `index()`, `show()`, `create()`, `store()`, `edit()`, `update()`, `destroy()`
  - Features:
    - List all users with pagination
    - Filter by role (resident/staff)
    - Search by name or email
    - View user details
    - Create new staff account with role assignment
    - Edit staff account details
    - Enable/disable users (soft delete or status flag)

#### Form Requests Needed
- `app/Http/Requests/StoreUserRequest.php` - Create user validation
- `app/Http/Requests/UpdateUserRequest.php` - Update user validation

#### Database Changes
- Add `status` column to users table if not already present (active/inactive)
- Or implement soft deletes with `SoftDeletes` trait

### 2. Complaint Management Interface
**Priority:** HIGH
**Status:** 30% Complete

#### Views Created
- `resources/views/admin/complaints.blade.php` - Complaint listing

#### Views Needed
- Enhanced filter/search UI component
- Complaint status override confirmation modal
- Reassign dialog

#### Controllers Needed
- Extend `app/Http/Controllers/Admin/WorkspaceController.php`
  - Method: `complaints()` - List all complaints with filters
  - Add filtering logic:
    - By status (Pending, In Progress, Resolved, Rejected)
    - By date range
    - By resident name
    - By assigned staff member
    - Search by title/description
  - Add search functionality with pagination
  - Support for bulk actions (reassign, change status)

#### Features to Add
- Advanced filtering with date picker
- Full-text search on complaint title/description
- Inline status change (admin override capability)
- Reassign complaint to different staff member
- View complaint detail with all evidence
- Add admin notes to complaint

### 3. Reports & Export
**Priority:** MEDIUM
**Status:** 10% Complete

#### Views Created
- `resources/views/reports/` directory structure

#### Views Needed
- `resources/views/admin/reports/index.blade.php` - Reports dashboard
- `resources/views/admin/reports/complaints.blade.php` - Complaint report
- `resources/views/admin/reports/staff-performance.blade.php` - Staff performance report
- `resources/views/admin/reports/residents.blade.php` - Resident statistics

#### Controllers Needed
- Create `app/Http/Controllers/Admin/ReportController.php`
  - Methods:
    - `index()` - Reports dashboard
    - `complaintReport()` - Generate complaint statistics
    - `staffPerformance()` - Staff resolution rates
    - `residentReport()` - Resident statistics
    - `exportPdf()` - Export to PDF
    - `exportCsv()` - Export to CSV

#### Export Implementation
Install required packages:
```bash
composer require barryvdh/laravel-dompdf
composer require maatwebsite/excel
```

Create export classes:
- `app/Exports/ComplaintsExport.php` - For CSV/Excel export
- `app/Exports/ReportsExport.php` - For general reports

Export features:
- Complaint report (PDF/CSV)
- Staff performance report (PDF/CSV)
- Resident statistics (PDF/CSV)
- Date range filtering
- Summary statistics

### 4. Barangay Information Management
**Priority:** MEDIUM
**Status:** 20% Complete

#### Views Needed
- `resources/views/admin/information.blade.php` - Edit page (already partially created)

#### Features to Add
- Form to edit:
  - Barangay name
  - Address
  - Contact number
  - Email
  - Hotline
  - Logo/image upload
  - Operating hours
  - Social media links

#### Controllers Needed
- Extend `app/Http/Controllers/Admin/WorkspaceController.php`
  - Method: `updateInformation()` - Save barangay info
  - Method: `editInformation()` - Show edit form

#### Database
- Model: `app/Models/BarangayInformation.php` (already exists)
- Migration: Ensure all required fields exist

### 5. System Activity Log
**Priority:** MEDIUM
**Status:** 10% Complete

#### Views Needed
- `resources/views/admin/activity.blade.php` - Activity log view (exists but needs completion)
  - Display all system activities (logins, complaints, approvals, file uploads)
  - Filter by:
    - User (who performed action)
    - Action type (login, create, update, delete, etc.)
    - Date range
  - Pagination
  - Export activity log

#### Controllers Needed
- Extend or create activity logging controller
  - Method: `index()` - List all activities with filters
  - Method: `export()` - Export activity log as CSV/PDF

#### Database
- Table: `complaint_activities` (exists)
- May need to expand to track other activities:
  - User logins/logouts
  - User management actions
  - System configuration changes
  - File uploads/deletions

#### Features
- Real-time activity feed
- Comprehensive audit trail
- User action tracking
- System change history

### 6. Admin Settings
**Priority:** LOW
**Status:** 5% Complete

#### Views Needed
- `resources/views/admin/settings.blade.php`
  - Appearance tab (theme, logo, colors)
  - Security tab (password policy, login attempts, throttling)
  - Email tab (test SMTP configuration)
  - System tab (app name, timezone, language)

#### Controllers Needed
- Create `app/Http/Controllers/Admin/SettingsController.php`
  - Method: `show()` - Display current settings
  - Method: `update()` - Update settings
  - Method: `testEmail()` - Test email configuration

#### Features
- Email configuration testing
- Logo/branding management
- Password policy configuration
- System timezone settings
- Application-wide settings persistence

---

## 📋 Implementation Roadmap

### Week 1: User Management
**Tasks:**
1. Create `UserController` with full CRUD
2. Create `StoreUserRequest` and `UpdateUserRequest` form requests
3. Create user detail, create, and edit views
4. Implement staff account creation from admin panel
5. Add user enable/disable functionality with database migration
6. Test user management with tests

**Expected Deliverable:** Full user management CRUD

### Week 2: Complaint Management Enhancement
**Tasks:**
1. Extend `WorkspaceController::complaints()` with filtering
2. Create reusable filter component
3. Implement search functionality
4. Add bulk action support
5. Implement admin complaint detail view with override options
6. Add reassign functionality to different staff
7. Test filtering and search

**Expected Deliverable:** Complete admin complaint management interface

### Week 3: Reports & Export
**Tasks:**
1. Install DomPDF and Laravel Excel
2. Create `ReportController` with all report methods
3. Create export classes for complaints and reports
4. Build report views with charts/statistics
5. Implement PDF export
6. Implement CSV export
7. Test all export formats

**Expected Deliverable:** Working reports and export functionality

### Week 4: Final Features & Polish
**Tasks:**
1. Complete barangay information management
2. Expand activity log with all system activities
3. Create admin settings interface
4. Test multi-user concurrent operations
5. Security audit and rate limiting review
6. Documentation and user guide
7. Final testing and bug fixes

**Expected Deliverable:** Complete Phase 4 with all features

---

## 🔧 Implementation Details

### Database Considerations

#### New Columns to Add
```php
// users table
Schema::table('users', function (Blueprint $table) {
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->softDeletes(); // For soft delete
    $table->timestamp('last_login_at')->nullable();
});

// complaints table enhancements
Schema::table('complaints', function (Blueprint $table) {
    $table->text('admin_notes')->nullable();
    $table->unsignedBigInteger('admin_override_by')->nullable();
    $table->timestamp('admin_override_at')->nullable();
});
```

### API Endpoints to Create
```
// User Management
GET    /admin/users                          # List users
GET    /admin/users/{id}                     # View user
POST   /admin/users                          # Create user
GET    /admin/users/{id}/edit                # Edit form
PATCH  /admin/users/{id}                     # Update user
DELETE /admin/users/{id}                     # Delete/disable user

// Complaint Management
GET    /admin/complaints                     # List all complaints
GET    /admin/complaints/{id}                # View complaint
PATCH  /admin/complaints/{id}/assign         # Reassign staff
POST   /admin/complaints/{id}/override       # Override status
POST   /admin/complaints/{id}/add-note       # Add admin note

// Reports
GET    /admin/reports                        # Reports dashboard
GET    /admin/reports/complaints             # Complaint report
GET    /admin/reports/complaints/export      # Export complaints
GET    /admin/reports/staff                  # Staff performance
GET    /admin/reports/residents              # Resident statistics

// Barangay Info
GET    /admin/information                    # View barangay info
PATCH  /admin/information                    # Update barangay info

// Activity Log
GET    /admin/activity                       # View activity log
GET    /admin/activity/export                # Export activity log

// Settings
GET    /admin/settings                       # View settings
PATCH  /admin/settings                       # Update settings
POST   /admin/settings/test-email            # Test email
```

### Form Validation Rules

#### Create User
```php
[
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'phone' => 'nullable|string|max:20',
    'role' => 'required|in:staff,resident',
    'password' => 'required|string|min:8|regex:/[A-Z]/|regex:/[a-z]/|regex:/[0-9]/|regex:/[^a-zA-Z0-9]/',
    'password_confirmation' => 'required|same:password',
]
```

#### Update User
```php
[
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email,' . $userId,
    'phone' => 'nullable|string|max:20',
    'status' => 'in:active,inactive',
]
```

### Testing Requirements

Tests to create:
```php
// UserControllerTest
- testCanListUsers()
- testCanViewUser()
- testCanCreateStaffUser()
- testCanUpdateUser()
- testCanDisableUser()
- testCannotCreateResidentAsAdmin()
- testValidationFailsWithoutEmail()

// ComplaintManagementTest
- testCanFilterComplaintsByStatus()
- testCanSearchComplaints()
- testCanAssignComplaint()
- testCanAddAdminNote()
- testAdminCanOverrideStatus()

// ReportControllerTest
- testCanGenerateComplaintReport()
- testCanExportToPdf()
- testCanExportToCsv()
- testCanFilterByDateRange()

// SettingsControllerTest
- testCanViewSettings()
- testCanUpdateSettings()
- testCanTestEmailConfiguration()
```

---

## 🎯 Success Criteria

### User Management
- ✅ Admin can list all users with pagination
- ✅ Admin can filter users by role
- ✅ Admin can create new staff accounts
- ✅ Admin can edit staff information
- ✅ Admin can disable/enable users
- ✅ Admin cannot create admin accounts through UI
- ✅ Proper authorization checks in place

### Complaint Management
- ✅ Admin can filter complaints by status, date, resident, staff
- ✅ Admin can search complaints by title/description
- ✅ Admin can view full complaint details with evidence
- ✅ Admin can reassign complaints to different staff
- ✅ Admin can override complaint status (with audit trail)
- ✅ Admin can add notes to complaints
- ✅ All changes logged in activity log

### Reports
- ✅ Reports dashboard shows key statistics
- ✅ Complaint reports with statistics and filtering
- ✅ Staff performance report (resolution rates, avg time)
- ✅ Resident statistics
- ✅ PDF export with proper formatting
- ✅ CSV export with data integrity
- ✅ Date range filtering for all reports

### Settings
- ✅ Barangay information management
- ✅ Logo/branding upload and display
- ✅ Email configuration with test capability
- ✅ Activity log viewing and export
- ✅ System-wide settings persistence

---

## 🚀 Next Steps

1. **Start with User Management** (highest priority)
   - Create UserController
   - Build user CRUD views
   - Test thoroughly

2. **Move to Complaint Management** (second priority)
   - Add filtering and search
   - Implement reassignment
   - Add admin override with audit

3. **Add Reports** (third priority)
   - Create report generator
   - Implement export functionality
   - Add visualization/statistics

4. **Final Polish** (fourth priority)
   - Settings and configuration
   - Activity log completion
   - Documentation and testing

---

## 📚 Reference Files

- Main dashboard: `resources/views/admin/dashboard.blade.php`
- Admin controller: `app/Http/Controllers/Admin/WorkspaceController.php`
- Routes: `routes/web.php` (admin section)
- Policies: `app/Policies/`
- Models: `app/Models/`

---

## 🔍 Notes

- All Phase 3 features (real-time updates, approval workflow) should continue working
- Real-time broadcasts should include admin status override actions
- Activity log should capture all admin actions
- Authorization checks must be strict (policies required for all sensitive operations)
- Tests are mandatory for all new features

---

**Last Updated:** 2025-01-31
**Phase:** 4 - Admin & Reporting
**Status:** Ready for implementation
