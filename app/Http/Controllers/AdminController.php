<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\Fest;
use App\Models\Blog;
use App\Models\Registration;
use App\Models\PrayerTime;
use App\Models\GalleryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin');
    }

    /**
     * Show the admin dashboard with comprehensive statistics
     */
    public function dashboard()
    {
        $cacheService = app(\App\Services\CacheService::class);
        
        // Get cached statistics for better performance
        $eventStats = $cacheService->getEventStatistics();
        $registrationStats = $cacheService->getRegistrationStatistics();
        $galleryStats = $cacheService->getGalleryStatistics();
        
        // System overview statistics (combine cached and non-cached)
        $stats = array_merge([
            'total_users' => User::count(),
            'total_fests' => Fest::count(),
            'total_blogs' => Blog::count(),
        ], $eventStats, $registrationStats, $galleryStats);

        // Recent activity statistics (not cached as they change frequently)
        $recentStats = [
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'new_events_this_month' => Event::whereMonth('created_at', now()->month)->count(),
            'new_registrations_this_month' => Registration::whereMonth('registered_at', now()->month)->count(),
            'new_blogs_this_month' => Blog::whereMonth('created_at', now()->month)->count(),
        ];

        // User role distribution
        $userRoleDistribution = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role')
            ->toArray();

        // Recent registrations (last 7 days)
        $recentRegistrations = Registration::with(['event', 'user'])
            ->where('registered_at', '>=', now()->subDays(7))
            ->orderBy('registered_at', 'desc')
            ->limit(10)
            ->get();

        // Upcoming events
        $upcomingEvents = Event::with('fest')
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->limit(5)
            ->get();

        // Popular events (by registration count)
        $popularEvents = Event::withCount('registrations')
            ->orderBy('registrations_count', 'desc')
            ->limit(5)
            ->get();

        // Monthly registration trends (last 6 months)
        $monthlyTrends = Registration::selectRaw('
                YEAR(registered_at) as year,
                MONTH(registered_at) as month,
                COUNT(*) as count
            ')
            ->where('registered_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::create($item->year, $item->month)->format('M Y'),
                    'count' => $item->count
                ];
            });

        // Revenue statistics
        $revenueStats = [
            'total_revenue' => Registration::where('payment_status', 'verified')->sum('payment_amount'),
            'pending_revenue' => Registration::where('payment_status', 'pending')->sum('payment_amount'),
            'this_month_revenue' => Registration::where('payment_status', 'verified')
                                              ->whereMonth('registered_at', now()->month)
                                              ->sum('payment_amount'),
        ];

        // System health indicators
        $systemHealth = [
            'active_fests' => Fest::where('status', 'published')->count(),
            'published_events' => Event::where('status', 'published')->count(),
            'recent_prayer_updates' => PrayerTime::where('updated_at', '>=', now()->subDays(7))->count(),
            'recent_gallery_uploads' => GalleryImage::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'recentStats',
            'registrationStats',
            'userRoleDistribution',
            'recentRegistrations',
            'upcomingEvents',
            'popularEvents',
            'monthlyTrends',
            'revenueStats',
            'systemHealth'
        ));
    }

    /**
     * Show user management interface
     */
    public function userManagement(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by registration status
        if ($request->filled('has_registrations')) {
            if ($request->has_registrations === 'yes') {
                $query->whereHas('registrations');
            } else {
                $query->whereDoesntHave('registrations');
            }
        }

        $users = $query->withCount(['registrations', 'authoredBlogs', 'authoredEvents'])
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);

        // Get role statistics
        $roleStats = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role')
            ->toArray();

        return view('admin.user-management', compact('users', 'roleStats'));
    }

    /**
     * Update user role
     */
    public function updateUserRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', Rule::in(['member', 'event_admin', 'content_admin', 'super_admin'])],
            'reason' => 'nullable|string|max:500',
        ]);

        $oldRole = $user->role;
        $user->update(['role' => $request->role]);

        // Log the role change
        $this->logAdminActivity('user_role_changed', [
            'user_id' => $user->id,
            'old_role' => $oldRole,
            'new_role' => $request->role,
            'reason' => $request->reason,
        ]);

        return redirect()->back()->with('success', "User role updated from {$oldRole} to {$request->role}.");
    }

    /**
     * Reset user password
     */
    public function resetUserPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
            'reason' => 'nullable|string|max:500',
        ]);

        $user->update(['password' => Hash::make($request->new_password)]);

        // Log the password reset
        $this->logAdminActivity('user_password_reset', [
            'user_id' => $user->id,
            'reason' => $request->reason,
        ]);

        return redirect()->back()->with('success', 'User password has been reset successfully.');
    }

    /**
     * Show system analytics
     */
    public function analytics(Request $request)
    {
        // Date range filter (default to last 30 days)
        $startDate = $request->filled('start_date') ? 
            Carbon::parse($request->start_date) : 
            now()->subDays(30);
        
        $endDate = $request->filled('end_date') ? 
            Carbon::parse($request->end_date) : 
            now();

        // User growth analytics
        $userGrowth = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('M j'),
                    'count' => $item->count
                ];
            });

        // Event participation trends
        $eventParticipation = Registration::with('event')
            ->whereBetween('registered_at', [$startDate, $endDate])
            ->get()
            ->groupBy('event.type')
            ->map(function ($registrations, $eventType) {
                return [
                    'event_type' => ucfirst($eventType),
                    'registrations' => $registrations->count(),
                    'participants' => $registrations->sum(function ($registration) {
                        return $registration->registration_type === 'team' 
                            ? $registration->getTeamMemberCount() + 1 
                            : 1;
                    })
                ];
            })
            ->values();

        // Popular events by registration
        $popularEvents = Event::withCount(['registrations' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('registered_at', [$startDate, $endDate]);
            }])
            ->having('registrations_count', '>', 0)
            ->orderBy('registrations_count', 'desc')
            ->limit(10)
            ->get();

        // Revenue analytics
        $revenueData = Registration::where('payment_status', 'verified')
            ->whereBetween('registered_at', [$startDate, $endDate])
            ->selectRaw('
                DATE(registered_at) as date,
                SUM(payment_amount) as revenue,
                COUNT(*) as paid_registrations
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('M j'),
                    'revenue' => $item->revenue,
                    'paid_registrations' => $item->paid_registrations
                ];
            });

        // Content creation analytics
        $contentStats = [
            'blogs_created' => Blog::whereBetween('created_at', [$startDate, $endDate])->count(),
            'events_created' => Event::whereBetween('created_at', [$startDate, $endDate])->count(),
            'fests_created' => Fest::whereBetween('created_at', [$startDate, $endDate])->count(),
            'images_uploaded' => GalleryImage::whereBetween('created_at', [$startDate, $endDate])->count(),
        ];

        // User engagement metrics
        $engagementMetrics = [
            'active_users' => User::whereHas('registrations', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('registered_at', [$startDate, $endDate]);
            })->count(),
            'repeat_participants' => User::whereHas('registrations', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('registered_at', [$startDate, $endDate]);
            }, '>=', 2)->count(),
            'new_user_registrations' => Registration::whereHas('user', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })->whereBetween('registered_at', [$startDate, $endDate])->count(),
        ];

        return view('admin.analytics', compact(
            'userGrowth',
            'eventParticipation',
            'popularEvents',
            'revenueData',
            'contentStats',
            'engagementMetrics',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Show admin activity logs
     */
    public function activityLogs(Request $request)
    {
        $query = DB::table('admin_activity_logs')
            ->join('users', 'admin_activity_logs.admin_user_id', '=', 'users.id')
            ->select('admin_activity_logs.*', 'users.name as admin_name', 'users.email as admin_email');

        // Filter by admin user
        if ($request->filled('admin_user_id')) {
            $query->where('admin_activity_logs.admin_user_id', $request->admin_user_id);
        }

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('admin_activity_logs.action', $request->action);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('admin_activity_logs.created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('admin_activity_logs.created_at', '<=', $request->end_date);
        }

        $logs = $query->orderBy('admin_activity_logs.created_at', 'desc')->paginate(50);

        // Get admin users for filter
        $adminUsers = User::whereIn('role', ['super_admin', 'content_admin', 'event_admin'])
            ->orderBy('name')
            ->get();

        // Get unique actions for filter
        $actions = DB::table('admin_activity_logs')
            ->distinct()
            ->pluck('action')
            ->sort()
            ->values();

        return view('admin.activity-logs', compact('logs', 'adminUsers', 'actions'));
    }

    /**
     * Show system settings management
     */
    public function systemSettings()
    {
        // Get current system settings (you might want to create a settings table)
        $settings = [
            'site_name' => config('app.name', 'Islamic Society Website'),
            'site_description' => 'Official website of the Islamic University of Technology Islamic Society',
            'contact_email' => 'contact@iutsiks.org',
            'max_registration_per_event' => 100,
            'default_event_fee' => 0,
            'payment_methods' => ['bkash', 'nagad', 'bank_transfer'],
            'gallery_max_file_size' => 2048, // KB
            'registration_deadline_days' => 7,
        ];

        return view('admin.system-settings', compact('settings'));
    }

    /**
     * Update system settings
     */
    public function updateSystemSettings(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'required|string|max:500',
            'contact_email' => 'required|email|max:255',
            'max_registration_per_event' => 'required|integer|min:1|max:1000',
            'default_event_fee' => 'required|numeric|min:0',
            'gallery_max_file_size' => 'required|integer|min:512|max:10240',
            'registration_deadline_days' => 'required|integer|min:1|max:30',
        ]);

        // In a real application, you would save these to a settings table or config files
        // For now, we'll just log the change
        $this->logAdminActivity('system_settings_updated', $request->only([
            'site_name', 'site_description', 'contact_email', 'max_registration_per_event',
            'default_event_fee', 'gallery_max_file_size', 'registration_deadline_days'
        ]));

        return redirect()->back()->with('success', 'System settings updated successfully.');
    }

    /**
     * Export system data
     */
    public function exportData(Request $request)
    {
        $request->validate([
            'export_type' => 'required|in:users,events,registrations,all',
            'format' => 'required|in:csv,json',
        ]);

        $filename = 'system_export_' . $request->export_type . '_' . now()->format('Y-m-d_H-i-s');
        
        if ($request->format === 'csv') {
            return $this->exportToCsv($request->export_type, $filename);
        } else {
            return $this->exportToJson($request->export_type, $filename);
        }
    }

    /**
     * Log admin activity
     */
    private function logAdminActivity(string $action, array $details = [])
    {
        DB::table('admin_activity_logs')->insert([
            'admin_user_id' => Auth::id(),
            'action' => $action,
            'details' => json_encode($details),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Export data to CSV format
     */
    private function exportToCsv(string $type, string $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function () use ($type) {
            $file = fopen('php://output', 'w');
            
            switch ($type) {
                case 'users':
                    fputcsv($file, ['ID', 'Name', 'Email', 'Role', 'Phone', 'Student ID', 'Created At']);
                    User::chunk(1000, function ($users) use ($file) {
                        foreach ($users as $user) {
                            fputcsv($file, [
                                $user->id,
                                $user->name,
                                $user->email,
                                $user->role,
                                $user->phone,
                                $user->student_id,
                                $user->created_at
                            ]);
                        }
                    });
                    break;
                    
                case 'events':
                    fputcsv($file, ['ID', 'Title', 'Type', 'Date', 'Location', 'Max Participants', 'Fee', 'Status']);
                    Event::with('fest')->chunk(1000, function ($events) use ($file) {
                        foreach ($events as $event) {
                            fputcsv($file, [
                                $event->id,
                                $event->title,
                                $event->type,
                                $event->event_date,
                                $event->location,
                                $event->max_participants,
                                $event->fee_amount,
                                $event->status
                            ]);
                        }
                    });
                    break;
                    
                case 'registrations':
                    fputcsv($file, ['ID', 'Event', 'User', 'Type', 'Status', 'Payment Status', 'Amount', 'Registered At']);
                    Registration::with(['event', 'user'])->chunk(1000, function ($registrations) use ($file) {
                        foreach ($registrations as $registration) {
                            fputcsv($file, [
                                $registration->id,
                                $registration->event->title,
                                $registration->user->name,
                                $registration->registration_type,
                                $registration->status,
                                $registration->payment_status,
                                $registration->payment_amount,
                                $registration->registered_at
                            ]);
                        }
                    });
                    break;
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export data to JSON format
     */
    private function exportToJson(string $type, string $filename)
    {
        $data = [];
        
        switch ($type) {
            case 'users':
                $data = User::all();
                break;
            case 'events':
                $data = Event::with('fest')->get();
                break;
            case 'registrations':
                $data = Registration::with(['event', 'user'])->get();
                break;
            case 'all':
                $data = [
                    'users' => User::all(),
                    'events' => Event::with('fest')->get(),
                    'registrations' => Registration::with(['event', 'user'])->get(),
                    'fests' => Fest::with('events')->get(),
                    'exported_at' => now()
                ];
                break;
        }

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}.json\"");
    }
}