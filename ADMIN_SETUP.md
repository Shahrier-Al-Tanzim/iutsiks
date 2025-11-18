# SIKS Admin Setup Guide

## Quick Setup

### 1. Create Admin Users

**Option A: Using the custom script (Recommended)**
```bash
php create_admin.php
```

**Option B: Using Artisan command**
```bash
php artisan siks:create-admin --all
```

**Option C: Using Tinker (when database is available)**
```bash
php artisan tinker
```
Then run:
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Super Admin',
    'email' => 'admin@siks.edu',
    'password' => Hash::make('admin123'),
    'role' => 'super_admin',
    'email_verified_at' => now()
]);
```

### 2. Default Admin Credentials

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| Super Admin | admin@siks.edu | admin123 | Full system access |
| Event Admin | events@siks.edu | events123 | Events & registrations |
| Content Admin | content@siks.edu | content123 | Blogs & prayer times |
| Prayer Admin | prayer@siks.edu | prayer123 | Prayer times only |

## Admin Access URLs

### Super Admin Dashboard
- **URL**: `/admin/dashboard`
- **Access**: Super admins only
- **Features**: 
  - System overview and statistics
  - User management
  - System settings
  - Activity logs
  - Data export

### User Management
- **URL**: `/admin/users`
- **Access**: Super admins only
- **Features**:
  - View all users
  - Change user roles
  - Reset passwords
  - User statistics

### Event & Registration Management
- **URL**: `/admin/registrations`
- **Access**: Super admins and event admins
- **Features**:
  - View all registrations
  - Approve/reject registrations
  - Payment verification
  - Registration analytics
  - Export registration data

### Prayer Times Management
- **URL**: `/admin/prayer-times`
- **Access**: Super admins and content admins
- **Features**:
  - Set daily prayer times
  - Bulk edit prayer times
  - View prayer times history
  - Prayer times for specific dates

### System Analytics
- **URL**: `/admin/analytics`
- **Access**: Super admins only
- **Features**:
  - User growth analytics
  - Event participation trends
  - Revenue analytics
  - Content creation statistics

### Activity Logs
- **URL**: `/admin/activity-logs`
- **Access**: Super admins only
- **Features**:
  - View all admin actions
  - Filter by admin user
  - Filter by action type
  - Date range filtering

## Role Permissions

### Super Admin (`super_admin`)
- ✅ Full system access
- ✅ User management
- ✅ System settings
- ✅ All admin features
- ✅ View analytics
- ✅ Activity logs
- ✅ Data export

### Event Admin (`event_admin`)
- ✅ Manage events
- ✅ Manage registrations
- ✅ Payment verification
- ✅ Registration analytics
- ✅ Gallery management
- ❌ User management
- ❌ System settings

### Content Admin (`content_admin`)
- ✅ Manage blogs
- ✅ Manage prayer times
- ✅ Gallery management
- ❌ Event management
- ❌ User management
- ❌ System settings

### Member (`member`)
- ✅ Register for events
- ✅ View public content
- ❌ Admin access

## Troubleshooting

### "Unauthorized Access" Error

1. **Check user role**:
   ```bash
   php artisan tinker
   User::where('email', 'your-email@example.com')->first()->role;
   ```

2. **Update user role**:
   ```bash
   php artisan tinker
   $user = User::where('email', 'your-email@example.com')->first();
   $user->role = 'super_admin';
   $user->save();
   ```

3. **Clear cache**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   ```

### Database Connection Issues

1. **Check .env file**:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

2. **Test database connection**:
   ```bash
   php artisan migrate:status
   ```

### Middleware Issues

1. **Check if middleware is registered**:
   - Role middleware should be in `bootstrap/app.php`
   - Gates should be defined in `AuthServiceProvider`

2. **Verify user authentication**:
   ```bash
   php artisan tinker
   auth()->check(); // Should return true when logged in
   auth()->user()->role; // Should return user role
   ```

## Security Notes

⚠️ **IMPORTANT**: Change default passwords immediately in production!

1. **Change admin passwords**:
   ```bash
   php artisan tinker
   $admin = User::where('email', 'admin@siks.edu')->first();
   $admin->password = Hash::make('your-secure-password');
   $admin->save();
   ```

2. **Enable additional security**:
   - Use strong passwords (12+ characters)
   - Consider implementing 2FA
   - Regularly audit admin access logs
   - Use HTTPS in production

## Quick Commands

```bash
# Create all admin users
php artisan siks:create-admin --all

# Create single admin user (interactive)
php artisan siks:create-admin

# Check admin routes
php artisan route:list --name=admin

# View current user roles
php artisan tinker --execute="User::select('name', 'email', 'role')->get()"

# Clear all caches
php artisan optimize:clear
```

## Admin Navigation

Once logged in as an admin:

1. **Via Dashboard**: Visit `/dashboard` and look for "Admin Panel" section
2. **Via User Dropdown**: Click your name in the top-right corner
3. **Direct URLs**: Navigate directly to admin URLs listed above

## Support

If you encounter issues:

1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify database connectivity
3. Ensure proper user roles are assigned
4. Clear application caches
5. Check middleware registration in `bootstrap/app.php`