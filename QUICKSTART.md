# Brgy. Poblacion CMS - Quick Start Guide

## 🚀 Project Setup (First Time)

### Prerequisites
- PHP 8.3+
- Composer
- Node.js & npm
- Git

### Installation Steps

```bash
# 1. Navigate to project directory
cd c:\Projects\Brgy_Pob_cms

# 2. Install PHP dependencies
composer install

# 3. Install JavaScript dependencies
npm install

# 4. Copy environment file
copy .env.example .env
# Edit .env and configure:
#   - APP_KEY (will be generated in next step)
#   - DATABASE (already set to sqlite)
#   - MAIL_* settings (Gmail SMTP)

# 5. Generate application key
php artisan key:generate

# 6. Create SQLite database
php artisan migrate --seed

# 7. Create symbolic link for file uploads
php artisan storage:link

# 8. Build frontend assets
npm run build
```

---

## 🎯 Running the Application

### Start All Services (4 Terminal Windows)

**Terminal 1: Laravel Reverb (Real-time Broadcasting)**
```bash
cd c:\Projects\Brgy_Pob_cms
php artisan reverb:start
```
Expected output: `[2025-01-31 10:00:00] Broadcasting ready. Waiting for connections...`

**Terminal 2: Queue Worker (Email Processing)**
```bash
cd c:\Projects\Brgy_Pob_cms
php artisan queue:work
```
Expected output: `Processing jobs from the [default] queue...`

**Terminal 3: Web Server**
```bash
cd c:\Projects\Brgy_Pob_cms
php artisan serve
```
Expected output: `Laravel development server started on [http://127.0.0.1:8000]`

**Terminal 4: Frontend Hot Reload (Optional - only if modifying CSS/JS)**
```bash
cd c:\Projects\Brgy_Pob_cms
npm run dev
```
Expected output: `VITE ... ready in ... ms`

### Access Points

| Role | URL | Email | Password |
|------|-----|-------|----------|
| **Resident** | http://localhost:8000 | new-resident@gmail.com (register) | your choice |
| **Staff** | http://localhost:8000/staff/login | [created by admin] | [created by admin] |
| **Admin** | http://localhost:8000/admin/login | admin@poblacion.local | ChangeMe123! |

---

## 📝 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
# Authentication tests only
php artisan test --filter=Auth

# Complaint tests only
php artisan test --filter=Complaint

# Workflow tests only
php artisan test --filter=Workflow

# With coverage
php artisan test --coverage
```

### Current Test Status
- ✅ **47/50 tests passing** (3 skipped, 0 failures)
- Coverage: Authentication, Complaints, Evidence Upload, Workflow, Authorization, Dashboard, Admin

---

## 🔧 Common Commands

### Database
```bash
# Reset database (delete and recreate)
php artisan migrate:refresh --seed

# View current migrations
php artisan migrate:status

# Rollback last migration
php artisan migrate:rollback
```

### Cache
```bash
# Clear all caches
php artisan cache:clear
php artisan route:cache --clear
php artisan config:clear

# Rebuild caches
php artisan route:cache
php artisan config:cache
```

### Storage
```bash
# Recreate storage symlink
php artisan storage:link

# Clean old uploaded files
php artisan storage:prune
```

### Maintenance
```bash
# Check application status
php artisan about

# List all routes
php artisan route:list --path=complaint

# Run tinker (PHP REPL)
php artisan tinker
```

---

## 📧 Gmail SMTP Configuration

To enable email functionality (password reset, notifications):

1. **Get Gmail App Password:**
   - Go to https://myaccount.google.com/security
   - Enable 2-Factor Authentication
   - Go to "App passwords" → Select "Mail" and "Windows Computer"
   - Copy the 16-character password

2. **Update .env file:**
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-16-character-app-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your-email@gmail.com
   ```

3. **Test email sending:**
   ```bash
   php artisan tinker
   >>> Mail::raw('Test email', function($message) { $message->to('recipient@gmail.com'); });
   ```

---

## 🐛 Troubleshooting

### Issue: "No application key has been generated"
```bash
php artisan key:generate
```

### Issue: "File not found in Vite manifest"
```bash
npm run build
# or for development:
npm run dev
```

### Issue: "Connection refused" when accessing site
- Ensure `php artisan serve` is running in Terminal 3
- Check URL is `http://localhost:8000` (not https)
- Try different port: `php artisan serve --port=8080`

### Issue: Broadcasting not working
- Ensure `php artisan reverb:start` is running in Terminal 1
- Check browser DevTools Console for Echo connection errors
- Verify `BROADCAST_CONNECTION=reverb` in `.env`

### Issue: Email not sending
- Confirm MAIL_* settings in `.env`
- Use Gmail app-specific password (not regular password)
- Check Laravel logs: `storage/logs/laravel.log`
- Ensure queue worker is running: `php artisan queue:work`

### Issue: File upload fails
- Ensure `php artisan storage:link` has been run
- Check storage directory permissions: `storage/app/private/` should be writable
- Verify upload limits in `php.ini`:
  ```ini
  upload_max_filesize = 100M
  post_max_size = 100M
  ```

### Issue: Can't login after password reset
- Ensure queue worker is running (emails need to be processed)
- Check email was received (check Gmail spam folder)
- Reset password link should work for 60 minutes

### Issue: Multi-guard session conflicts
- Clear browser cookies for localhost
- Ensure you're using different browsers or private windows for different roles
- Try logging in as different roles in different browser profiles

---

## 📊 Database Schema Overview

### Core Tables
- **users** - All users (residents, staff, admin) with role field
- **complaints** - Submitted complaints with status and assignments
- **complaint_evidences** - Attached photos/videos with file paths
- **complaint_activities** - Activity log for all status changes
- **barangay_information** - System settings and information
- **notifications** - System notifications (optional)

### Roles
- **resident** - Can submit complaints and view own complaints
- **staff** - Can view assigned complaints, approve/reject, resolve
- **admin** - Can manage everything, create staff, view reports

---

## 🎨 Customization

### Colors (Tailwind)
Edit `tailwind.config.js`:
```javascript
colors: {
  'maroon': '#7B1E2B',
  'maroon-dark': '#5A1420',
  'gold': '#D4A017',
}
```

### Logo/Branding
- Update logo in `resources/views/partials/navbar.blade.php`
- Update app name in `config/app.php` and `.env`

### Email Templates
- Password reset: `resources/views/emails/`
- Notification emails: `app/Mail/`

### Form Validation Rules
- Complaints: `app/Http/Requests/StoreComplaintRequest.php`
- Evidence: `app/Http/Requests/StoreEvidenceRequest.php`
- User actions: `app/Http/Requests/`

---

## 📱 Browser Compatibility

### Tested and Supported
- ✅ Chrome/Edge 90+
- ✅ Firefox 88+
- ✅ Safari 14+

### Required Features
- LocalStorage (for theme preference)
- WebSockets (for real-time updates)
- File API (for uploads)
- HTML5 Video/Audio (for media playback)

---

## 🔒 Security Notes

### Login Throttling
- Resident login: 5 attempts per minute
- Staff login: 5 attempts per minute
- Admin login: 3 attempts per minute (stricter)

### Password Policy
- Minimum 8 characters
- Must contain uppercase, lowercase, number, and symbol
- Hashed with BCrypt

### File Upload Security
- Files stored in private storage (not web-accessible directly)
- Served only through authorized route
- MIME type validated
- Filename sanitized

### CSRF Protection
- All forms must include `@csrf` token
- All API routes require CSRF token in headers

---

## 📈 Performance Tips

### For Development
- Use `npm run dev` for hot reload during CSS/JS changes
- Use `php artisan serve` (not production server)
- Enable query debugging in `.env`: `DB_LOG_QUERIES=true`

### For Production (Not Covered Here)
- Use proper web server (Nginx/Apache)
- Cache config and routes: `php artisan config:cache`
- Use Redis for caching: `CACHE_STORE=redis`
- Use proper process manager: Supervisor
- Enable HTTPS: Update `APP_URL` and `.env`

---

## 📚 Project Structure Reference

```
resources/views/
├── admin/               # Admin panel pages
├── auth/                # Login/register pages
├── complaints/          # Complaint CRUD pages
├── resident/            # Resident dashboard/pages
├── staff/               # Staff dashboard/pages
├── profile/             # Profile management
├── reports/             # Reports/exports
├── layouts/             # Layout templates
└── partials/            # Reusable components

app/Http/Controllers/
├── Admin/               # Admin actions
├── Auth/                # Authentication
├── Resident/            # Resident actions
├── Staff/               # Staff actions
├── Settings/            # Settings management
├── ComplaintController.php
├── ComplaintEvidenceController.php
└── ComplaintWorkflowController.php

database/
├── migrations/          # Database schema
├── seeders/             # Initial data
└── factories/           # Test data generators
```

---

## 🤝 Support

For issues or questions:
1. Check the troubleshooting section above
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check browser console for errors: F12 → Console
4. Review database: `database/database.sqlite`

---

**Last Updated:** 2025-01-31
**Version:** Phase 3 Complete
**Status:** 📘 Ready for Phase 4 Development
