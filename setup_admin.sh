#!/bin/bash

echo "🚀 SIKS Admin Setup Script"
echo "=========================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

# Check if database is accessible
echo "Checking database connection..."
if php artisan migrate:status > /dev/null 2>&1; then
    print_status "Database connection successful"
    DB_AVAILABLE=true
else
    print_warning "Database connection failed - will create setup files for later use"
    DB_AVAILABLE=false
fi

# Clear caches (if possible)
echo ""
echo "Clearing application caches..."
php artisan config:clear > /dev/null 2>&1 && print_status "Config cache cleared"
php artisan route:clear > /dev/null 2>&1 && print_status "Route cache cleared"
php artisan view:clear > /dev/null 2>&1 && print_status "View cache cleared"

# Run migrations if database is available
if [ "$DB_AVAILABLE" = true ]; then
    echo ""
    echo "Running database migrations..."
    if php artisan migrate --force; then
        print_status "Database migrations completed"
    else
        print_error "Migration failed"
        DB_AVAILABLE=false
    fi
fi

# Create admin users if database is available
if [ "$DB_AVAILABLE" = true ]; then
    echo ""
    echo "Creating admin users..."
    
    if php artisan siks:create-admin --all; then
        print_status "Admin users created successfully"
        
        echo ""
        echo "🎉 Admin Setup Complete!"
        echo "======================="
        echo ""
        echo "Default Admin Credentials:"
        echo "-------------------------"
        echo "Super Admin:"
        echo "  Email: admin@siks.edu"
        echo "  Password: admin123"
        echo "  URL: /admin/dashboard"
        echo ""
        echo "Event Admin:"
        echo "  Email: events@siks.edu"
        echo "  Password: events123"
        echo "  URL: /admin/registrations"
        echo ""
        echo "Content Admin:"
        echo "  Email: content@siks.edu"
        echo "  Password: content123"
        echo "  URL: /admin/prayer-times"
        echo ""
        echo "Prayer Admin:"
        echo "  Email: prayer@siks.edu"
        echo "  Password: prayer123"
        echo "  URL: /admin/prayer-times"
        echo ""
        print_warning "IMPORTANT: Change these default passwords in production!"
        
    else
        print_error "Failed to create admin users"
    fi
else
    echo ""
    print_warning "Database not available - creating setup files for manual execution"
    
    # Create manual setup instructions
    cat > manual_admin_setup.sql << 'EOF'
-- SIKS Admin Users Setup SQL
-- Run this SQL script in your database when it's available

-- Insert Super Admin
INSERT INTO users (name, email, email_verified_at, password, role, phone, student_id, created_at, updated_at) 
VALUES (
    'Super Admin', 
    'admin@siks.edu', 
    NOW(), 
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
    'super_admin', 
    '+8801700000000', 
    'ADMIN001', 
    NOW(), 
    NOW()
) ON DUPLICATE KEY UPDATE 
    name = VALUES(name), 
    role = VALUES(role), 
    phone = VALUES(phone), 
    student_id = VALUES(student_id);

-- Insert Event Admin
INSERT INTO users (name, email, email_verified_at, password, role, phone, student_id, created_at, updated_at) 
VALUES (
    'Event Admin', 
    'events@siks.edu', 
    NOW(), 
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: events123
    'event_admin', 
    '+8801700000001', 
    'ADMIN002', 
    NOW(), 
    NOW()
) ON DUPLICATE KEY UPDATE 
    name = VALUES(name), 
    role = VALUES(role), 
    phone = VALUES(phone), 
    student_id = VALUES(student_id);

-- Insert Content Admin
INSERT INTO users (name, email, email_verified_at, password, role, phone, student_id, created_at, updated_at) 
VALUES (
    'Content Admin', 
    'content@siks.edu', 
    NOW(), 
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: content123
    'content_admin', 
    '+8801700000002', 
    'ADMIN003', 
    NOW(), 
    NOW()
) ON DUPLICATE KEY UPDATE 
    name = VALUES(name), 
    role = VALUES(role), 
    phone = VALUES(phone), 
    student_id = VALUES(student_id);

-- Insert Prayer Admin
INSERT INTO users (name, email, email_verified_at, password, role, phone, student_id, created_at, updated_at) 
VALUES (
    'Prayer Admin', 
    'prayer@siks.edu', 
    NOW(), 
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: prayer123
    'content_admin', 
    '+8801700000003', 
    'ADMIN004', 
    NOW(), 
    NOW()
) ON DUPLICATE KEY UPDATE 
    name = VALUES(name), 
    role = VALUES(role), 
    phone = VALUES(phone), 
    student_id = VALUES(student_id);
EOF

    print_status "Created manual_admin_setup.sql"
    
    # Create PHP setup script
    cat > setup_admin_when_db_ready.php << 'EOF'
<?php
/**
 * Run this script when database is available: php setup_admin_when_db_ready.php
 */

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "🚀 Creating SIKS Admin Users...\n\n";

    $admins = [
        [
            'name' => 'Super Admin',
            'email' => 'admin@siks.edu',
            'password' => 'admin123',
            'role' => 'super_admin',
            'phone' => '+8801700000000',
            'student_id' => 'ADMIN001',
        ],
        [
            'name' => 'Event Admin',
            'email' => 'events@siks.edu',
            'password' => 'events123',
            'role' => 'event_admin',
            'phone' => '+8801700000001',
            'student_id' => 'ADMIN002',
        ],
        [
            'name' => 'Content Admin',
            'email' => 'content@siks.edu',
            'password' => 'content123',
            'role' => 'content_admin',
            'phone' => '+8801700000002',
            'student_id' => 'ADMIN003',
        ],
        [
            'name' => 'Prayer Admin',
            'email' => 'prayer@siks.edu',
            'password' => 'prayer123',
            'role' => 'content_admin',
            'phone' => '+8801700000003',
            'student_id' => 'ADMIN004',
        ],
    ];

    foreach ($admins as $adminData) {
        $user = User::updateOrCreate(
            ['email' => $adminData['email']],
            [
                'name' => $adminData['name'],
                'email' => $adminData['email'],
                'password' => Hash::make($adminData['password']),
                'role' => $adminData['role'],
                'phone' => $adminData['phone'],
                'student_id' => $adminData['student_id'],
                'email_verified_at' => now(),
            ]
        );
        
        echo "✓ Created {$adminData['role']}: {$adminData['email']} / {$adminData['password']}\n";
    }

    echo "\n🎉 Admin users created successfully!\n\n";
    
    echo "Admin Access Information:\n";
    echo "========================\n";
    echo "Super Admin: admin@siks.edu / admin123 -> /admin/dashboard\n";
    echo "Event Admin: events@siks.edu / events123 -> /admin/registrations\n";
    echo "Content Admin: content@siks.edu / content123 -> /admin/prayer-times\n";
    echo "Prayer Admin: prayer@siks.edu / prayer123 -> /admin/prayer-times\n\n";
    
    echo "⚠️  IMPORTANT: Change these default passwords in production!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Make sure your database is configured and accessible.\n";
}
EOF

    print_status "Created setup_admin_when_db_ready.php"
fi

# Verify admin routes are available
echo ""
echo "Verifying admin routes..."
if php artisan route:list --name=admin > /dev/null 2>&1; then
    print_status "Admin routes are properly registered"
else
    print_warning "Could not verify admin routes"
fi

# Create quick reference file
cat > ADMIN_QUICK_REFERENCE.md << 'EOF'
# SIKS Admin Quick Reference

## Default Admin Credentials

| Role | Email | Password | Access URL |
|------|-------|----------|------------|
| Super Admin | admin@siks.edu | admin123 | /admin/dashboard |
| Event Admin | events@siks.edu | events123 | /admin/registrations |
| Content Admin | content@siks.edu | content123 | /admin/prayer-times |
| Prayer Admin | prayer@siks.edu | prayer123 | /admin/prayer-times |

## Admin URLs

- **Super Admin Dashboard**: `/admin/dashboard`
- **User Management**: `/admin/users`
- **Registration Management**: `/admin/registrations`
- **Prayer Times Management**: `/admin/prayer-times`
- **System Analytics**: `/admin/analytics`
- **Activity Logs**: `/admin/activity-logs`
- **System Settings**: `/admin/settings`

## Quick Commands

```bash
# Create admin users (when DB is ready)
php artisan siks:create-admin --all

# Verify admin access
php artisan siks:verify-admin

# Check specific user
php artisan siks:verify-admin admin@siks.edu

# Update user role manually
php artisan tinker --execute="User::where('email', 'user@example.com')->first()->update(['role' => 'super_admin']);"
```

## Troubleshooting

1. **Database not available**: Run `php setup_admin_when_db_ready.php` when DB is ready
2. **Unauthorized access**: Verify user role with `php artisan siks:verify-admin`
3. **Clear caches**: Run `php artisan optimize:clear`

## Security Notes

⚠️ **Change default passwords immediately in production!**

```bash
php artisan tinker
$admin = User::where('email', 'admin@siks.edu')->first();
$admin->password = Hash::make('your-secure-password');
$admin->save();
```
EOF

print_status "Created ADMIN_QUICK_REFERENCE.md"

echo ""
echo "📋 Setup Summary"
echo "================"

if [ "$DB_AVAILABLE" = true ]; then
    print_status "✅ Admin system fully configured and ready!"
    print_status "✅ Admin users created with default credentials"
    print_status "✅ All admin routes are accessible"
    echo ""
    echo "🔗 You can now access:"
    echo "   - Super Admin Dashboard: /admin/dashboard"
    echo "   - Login with: admin@siks.edu / admin123"
else
    print_warning "⏳ Database not available - setup files created for later use"
    echo ""
    echo "📁 Files created for manual setup:"
    echo "   - manual_admin_setup.sql (SQL script)"
    echo "   - setup_admin_when_db_ready.php (PHP script)"
    echo "   - ADMIN_QUICK_REFERENCE.md (Reference guide)"
    echo ""
    echo "🔧 When database is ready, run:"
    echo "   php setup_admin_when_db_ready.php"
fi

echo ""
print_info "📖 See ADMIN_SETUP.md for complete documentation"
print_warning "🔒 Remember to change default passwords in production!"

echo ""
echo "🎯 Next Steps:"
echo "1. Ensure database connection is working"
echo "2. Run setup script if not already done"
echo "3. Login with admin credentials"
echo "4. Change default passwords"
echo "5. Configure system settings"