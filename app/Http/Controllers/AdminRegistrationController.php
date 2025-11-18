<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Event;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminRegistrationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->middleware('auth');
        $this->middleware('role:super_admin,event_admin');
    }

    /**
     * Display all registrations with filtering options
     */
    public function index(Request $request)
    {
        $query = Registration::with([
            'event:id,fest_id,title,event_date,type,fee_amount,max_participants',
            'event.fest:id,title',
            'user:id,name,email,phone,student_id'
        ])->select('id', 'event_id', 'user_id', 'registration_type', 'team_name', 'team_members_json', 'payment_required', 'payment_amount', 'payment_status', 'status', 'registered_at');

        // Filter by event
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by registration type
        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        // Search by participant name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('individual_name', 'like', "%{$search}%")
                  ->orWhere('team_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Order by most recent first
        $registrations = $query->orderBy('registered_at', 'desc')->paginate(20);

        // Get events for filter dropdown
        $events = Event::with('fest')->orderBy('event_date', 'desc')->get();

        // Get statistics
        $stats = $this->getRegistrationStats();

        return view('admin.registrations.index', compact('registrations', 'events', 'stats'));
    }

    /**
     * Show registration details
     */
    public function show(Registration $registration)
    {
        $registration->load([
            'event:id,fest_id,title,description,event_date,event_time,type,registration_type,location,max_participants,fee_amount,registration_deadline,status',
            'event.fest:id,title,description,start_date,end_date',
            'user:id,name,email,phone,student_id,role'
        ]);
        
        return view('admin.registrations.show', compact('registration'));
    }

    /**
     * Show payment verification interface
     */
    public function paymentVerification(Request $request)
    {
        $query = Registration::with(['event', 'event.fest', 'user'])
                            ->where('payment_required', true)
                            ->where('payment_status', 'pending');

        // Filter by event
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        $pendingPayments = $query->orderBy('registered_at', 'asc')->paginate(20);

        // Get events for filter dropdown
        $events = Event::whereHas('registrations', function ($q) {
            $q->where('payment_required', true)
              ->where('payment_status', 'pending');
        })->with('fest')->orderBy('event_date', 'desc')->get();

        return view('admin.registrations.payment-verification', compact('pendingPayments', 'events'));
    }

    /**
     * Approve payment
     */
    public function approvePayment(Request $request, Registration $registration)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if (!$registration->payment_required || $registration->payment_status !== 'pending') {
            return redirect()->back()->with('error', 'Payment cannot be approved at this time.');
        }

        DB::transaction(function () use ($registration, $request) {
            $registration->update([
                'payment_status' => 'verified',
                'admin_notes' => $request->admin_notes ? 
                    ($registration->admin_notes ? $registration->admin_notes . "\n\n" : '') . 
                    "Payment approved by " . Auth::user()->name . " on " . now()->format('Y-m-d H:i:s') . ": " . $request->admin_notes
                    : ($registration->admin_notes ? $registration->admin_notes . "\n\n" : '') . 
                    "Payment approved by " . Auth::user()->name . " on " . now()->format('Y-m-d H:i:s'),
            ]);

            // Create audit log
            $this->createAuditLog($registration, 'payment_approved', $request->admin_notes);
        });

        // Send notification to user
        $this->notificationService->sendPaymentApprovalNotification($registration);

        return redirect()->back()->with('success', 'Payment approved successfully. User has been notified.');
    }

    /**
     * Reject payment
     */
    public function rejectPayment(Request $request, Registration $registration)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if (!$registration->payment_required || $registration->payment_status !== 'pending') {
            return redirect()->back()->with('error', 'Payment cannot be rejected at this time.');
        }

        DB::transaction(function () use ($registration, $request) {
            $registration->update([
                'payment_status' => 'rejected',
                'admin_notes' => ($registration->admin_notes ? $registration->admin_notes . "\n\n" : '') . 
                    "Payment rejected by " . Auth::user()->name . " on " . now()->format('Y-m-d H:i:s') . ": " . $request->rejection_reason,
            ]);

            // Create audit log
            $this->createAuditLog($registration, 'payment_rejected', $request->rejection_reason);
        });

        // Send notification to user
        $this->notificationService->sendPaymentRejectionNotification($registration, $request->rejection_reason);

        return redirect()->back()->with('success', 'Payment rejected. User has been notified and can resubmit payment details.');
    }

    /**
     * Approve registration
     */
    public function approveRegistration(Request $request, Registration $registration)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if (!$registration->canBeApproved()) {
            return redirect()->back()->with('error', 'Registration cannot be approved at this time. Check payment status if required.');
        }

        DB::transaction(function () use ($registration, $request) {
            $registration->update([
                'status' => 'approved',
                'admin_notes' => $request->admin_notes ? 
                    ($registration->admin_notes ? $registration->admin_notes . "\n\n" : '') . 
                    "Registration approved by " . Auth::user()->name . " on " . now()->format('Y-m-d H:i:s') . ": " . $request->admin_notes
                    : ($registration->admin_notes ? $registration->admin_notes . "\n\n" : '') . 
                    "Registration approved by " . Auth::user()->name . " on " . now()->format('Y-m-d H:i:s'),
            ]);

            // Create audit log
            $this->createAuditLog($registration, 'registration_approved', $request->admin_notes);
        });

        // Send notification to user
        $this->notificationService->sendRegistrationApprovalNotification($registration);

        return redirect()->back()->with('success', 'Registration approved successfully. User has been notified.');
    }

    /**
     * Reject registration
     */
    public function rejectRegistration(Request $request, Registration $registration)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($registration->status !== 'pending') {
            return redirect()->back()->with('error', 'Registration cannot be rejected at this time.');
        }

        DB::transaction(function () use ($registration, $request) {
            $registration->update([
                'status' => 'rejected',
                'admin_notes' => ($registration->admin_notes ? $registration->admin_notes . "\n\n" : '') . 
                    "Registration rejected by " . Auth::user()->name . " on " . now()->format('Y-m-d H:i:s') . ": " . $request->rejection_reason,
            ]);

            // Create audit log
            $this->createAuditLog($registration, 'registration_rejected', $request->rejection_reason);
        });

        // Send notification to user
        $this->notificationService->sendRegistrationRejectionNotification($registration, $request->rejection_reason);

        return redirect()->back()->with('success', 'Registration rejected. User has been notified.');
    }

    /**
     * Bulk approve payments
     */
    public function bulkApprovePayments(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:registrations,id',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $approvedCount = 0;
        $errors = [];

        DB::transaction(function () use ($request, &$approvedCount, &$errors) {
            foreach ($request->registration_ids as $registrationId) {
                $registration = Registration::find($registrationId);
                
                if ($registration && $registration->payment_required && $registration->payment_status === 'pending') {
                    $registration->update([
                        'payment_status' => 'verified',
                        'admin_notes' => $request->admin_notes ? 
                            ($registration->admin_notes ? $registration->admin_notes . "\n\n" : '') . 
                            "Payment bulk approved by " . Auth::user()->name . " on " . now()->format('Y-m-d H:i:s') . ": " . $request->admin_notes
                            : ($registration->admin_notes ? $registration->admin_notes . "\n\n" : '') . 
                            "Payment bulk approved by " . Auth::user()->name . " on " . now()->format('Y-m-d H:i:s'),
                    ]);

                    // Create audit log
                    $this->createAuditLog($registration, 'payment_bulk_approved', $request->admin_notes);

                    // Send notification to user
                    $this->notificationService->sendPaymentApprovalNotification($registration);

                    $approvedCount++;
                } else {
                    $errors[] = "Registration #{$registrationId} could not be approved.";
                }
            }
        });

        $message = "Successfully approved {$approvedCount} payments.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Bulk approve registrations
     */
    public function bulkApproveRegistrations(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:registrations,id',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $approvedCount = 0;
        $errors = [];

        DB::transaction(function () use ($request, &$approvedCount, &$errors) {
            foreach ($request->registration_ids as $registrationId) {
                $registration = Registration::find($registrationId);
                
                if ($registration && $registration->canBeApproved()) {
                    $registration->update([
                        'status' => 'approved',
                        'admin_notes' => $request->admin_notes ? 
                            ($registration->admin_notes ? $registration->admin_notes . "\n\n" : '') . 
                            "Registration bulk approved by " . Auth::user()->name . " on " . now()->format('Y-m-d H:i:s') . ": " . $request->admin_notes
                            : ($registration->admin_notes ? $registration->admin_notes . "\n\n" : '') . 
                            "Registration bulk approved by " . Auth::user()->name . " on " . now()->format('Y-m-d H:i:s'),
                    ]);

                    // Create audit log
                    $this->createAuditLog($registration, 'registration_bulk_approved', $request->admin_notes);

                    // Send notification to user
                    $this->notificationService->sendRegistrationApprovalNotification($registration);

                    $approvedCount++;
                } else {
                    $errors[] = "Registration #{$registrationId} could not be approved.";
                }
            }
        });

        $message = "Successfully approved {$approvedCount} registrations.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Bulk reject registrations
     */
    public function bulkRejectRegistrations(Request $request)
    {
        $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:registrations,id',
            'rejection_reason' => 'required|string|max:500',
        ]);

        $rejectedCount = 0;
        $errors = [];

        DB::transaction(function () use ($request, &$rejectedCount, &$errors) {
            foreach ($request->registration_ids as $registrationId) {
                $registration = Registration::find($registrationId);
                
                if ($registration && $registration->status === 'pending') {
                    $registration->update([
                        'status' => 'rejected',
                        'admin_notes' => ($registration->admin_notes ? $registration->admin_notes . "\n\n" : '') . 
                            "Registration bulk rejected by " . Auth::user()->name . " on " . now()->format('Y-m-d H:i:s') . ": " . $request->rejection_reason,
                    ]);

                    // Create audit log
                    $this->createAuditLog($registration, 'registration_bulk_rejected', $request->rejection_reason);

                    // Send notification to user
                    $this->notificationService->sendRegistrationRejectionNotification($registration, $request->rejection_reason);

                    $rejectedCount++;
                } else {
                    $errors[] = "Registration #{$registrationId} could not be rejected.";
                }
            }
        });

        $message = "Successfully rejected {$rejectedCount} registrations.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Export registrations to CSV
     */
    public function export(Request $request, ?Event $event = null)
    {
        $query = Registration::with(['event', 'event.fest', 'user']);

        if ($event) {
            $query->where('event_id', $event->id);
        }

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        $registrations = $query->orderBy('registered_at', 'desc')->get();

        $filename = 'registrations_' . ($event ? $event->title . '_' : '') . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($registrations) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Registration ID',
                'Event',
                'Fest',
                'Participant Name',
                'Email',
                'Phone',
                'Student ID',
                'Registration Type',
                'Team Name',
                'Team Size',
                'Payment Required',
                'Payment Amount',
                'Payment Status',
                'Payment Method',
                'Transaction ID',
                'Payment Date',
                'Status',
                'Registered At',
                'Admin Notes'
            ]);

            // CSV data
            foreach ($registrations as $registration) {
                fputcsv($file, [
                    $registration->id,
                    $registration->event->title,
                    $registration->event->fest ? $registration->event->fest->title : '',
                    $registration->getParticipantName(),
                    $registration->user->email,
                    $registration->user->phone ?? '',
                    $registration->user->student_id ?? '',
                    ucfirst($registration->registration_type),
                    $registration->team_name ?? '',
                    $registration->registration_type === 'team' ? $registration->getTeamMemberCount() + 1 : 1,
                    $registration->payment_required ? 'Yes' : 'No',
                    $registration->payment_amount,
                    ucfirst($registration->payment_status),
                    $registration->payment_method ?? '',
                    $registration->transaction_id ?? '',
                    $registration->payment_date ? $registration->payment_date->format('Y-m-d') : '',
                    ucfirst($registration->status),
                    $registration->registered_at->format('Y-m-d H:i:s'),
                    $registration->admin_notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show registration analytics dashboard
     */
    public function analytics(Request $request)
    {
        // Date range filter (default to last 30 days)
        $startDate = $request->filled('start_date') ? 
            \Carbon\Carbon::parse($request->start_date) : 
            now()->subDays(30);
        
        $endDate = $request->filled('end_date') ? 
            \Carbon\Carbon::parse($request->end_date) : 
            now();

        // Basic statistics
        $stats = $this->getRegistrationStats();
        
        // Registration trends over time
        $registrationTrends = $this->getRegistrationTrends($startDate, $endDate);
        
        // Popular events
        $popularEvents = $this->getPopularEvents($startDate, $endDate);
        
        // Payment analytics
        $paymentAnalytics = $this->getPaymentAnalytics($startDate, $endDate);
        
        // Registration type distribution
        $registrationTypeDistribution = $this->getRegistrationTypeDistribution($startDate, $endDate);
        
        // Event type participation
        $eventTypeParticipation = $this->getEventTypeParticipation($startDate, $endDate);
        
        // Monthly revenue
        $monthlyRevenue = $this->getMonthlyRevenue($startDate, $endDate);

        return view('admin.registrations.analytics', compact(
            'stats',
            'registrationTrends',
            'popularEvents',
            'paymentAnalytics',
            'registrationTypeDistribution',
            'eventTypeParticipation',
            'monthlyRevenue',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get registration statistics
     */
    private function getRegistrationStats()
    {
        return [
            'total_registrations' => Registration::count(),
            'pending_registrations' => Registration::where('status', 'pending')->count(),
            'approved_registrations' => Registration::where('status', 'approved')->count(),
            'rejected_registrations' => Registration::where('status', 'rejected')->count(),
            'pending_payments' => Registration::where('payment_required', true)
                                            ->where('payment_status', 'pending')->count(),
            'verified_payments' => Registration::where('payment_required', true)
                                             ->where('payment_status', 'verified')->count(),
            'rejected_payments' => Registration::where('payment_required', true)
                                             ->where('payment_status', 'rejected')->count(),
        ];
    }

    /**
     * Get registration trends over time
     */
    private function getRegistrationTrends($startDate, $endDate)
    {
        return Registration::selectRaw('DATE(registered_at) as date, COUNT(*) as count')
            ->whereBetween('registered_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => \Carbon\Carbon::parse($item->date)->format('M j'),
                    'count' => $item->count
                ];
            });
    }

    /**
     * Get popular events by registration count
     */
    private function getPopularEvents($startDate, $endDate)
    {
        return Registration::with(['event', 'event.fest'])
            ->selectRaw('event_id, COUNT(*) as registration_count')
            ->whereBetween('registered_at', [$startDate, $endDate])
            ->groupBy('event_id')
            ->orderByDesc('registration_count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'event_title' => $item->event->title,
                    'fest_title' => $item->event->fest ? $item->event->fest->title : null,
                    'registration_count' => $item->registration_count,
                    'event_date' => $item->event->event_date
                ];
            });
    }

    /**
     * Get payment analytics
     */
    private function getPaymentAnalytics($startDate, $endDate)
    {
        $paymentStats = Registration::where('payment_required', true)
            ->whereBetween('registered_at', [$startDate, $endDate])
            ->selectRaw('
                payment_status,
                COUNT(*) as count,
                SUM(payment_amount) as total_amount
            ')
            ->groupBy('payment_status')
            ->get();

        $totalRevenue = Registration::where('payment_required', true)
            ->where('payment_status', 'verified')
            ->whereBetween('registered_at', [$startDate, $endDate])
            ->sum('payment_amount');

        $pendingRevenue = Registration::where('payment_required', true)
            ->where('payment_status', 'pending')
            ->whereBetween('registered_at', [$startDate, $endDate])
            ->sum('payment_amount');

        return [
            'payment_stats' => $paymentStats,
            'total_revenue' => $totalRevenue,
            'pending_revenue' => $pendingRevenue,
            'payment_methods' => Registration::where('payment_required', true)
                ->where('payment_status', 'verified')
                ->whereBetween('registered_at', [$startDate, $endDate])
                ->selectRaw('payment_method, COUNT(*) as count')
                ->groupBy('payment_method')
                ->get()
        ];
    }

    /**
     * Get registration type distribution
     */
    private function getRegistrationTypeDistribution($startDate, $endDate)
    {
        return Registration::selectRaw('registration_type, COUNT(*) as count')
            ->whereBetween('registered_at', [$startDate, $endDate])
            ->groupBy('registration_type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => ucfirst($item->registration_type),
                    'count' => $item->count
                ];
            });
    }

    /**
     * Get event type participation
     */
    private function getEventTypeParticipation($startDate, $endDate)
    {
        return Registration::with('event')
            ->whereBetween('registered_at', [$startDate, $endDate])
            ->get()
            ->groupBy('event.type')
            ->map(function ($registrations, $eventType) {
                return [
                    'event_type' => ucfirst($eventType),
                    'count' => $registrations->count(),
                    'participants' => $registrations->sum(function ($registration) {
                        return $registration->registration_type === 'team' 
                            ? $registration->getTeamMemberCount() + 1 
                            : 1;
                    })
                ];
            })
            ->values();
    }

    /**
     * Get monthly revenue data
     */
    private function getMonthlyRevenue($startDate, $endDate)
    {
        return Registration::where('payment_required', true)
            ->where('payment_status', 'verified')
            ->whereBetween('registered_at', [$startDate, $endDate])
            ->selectRaw('
                YEAR(registered_at) as year,
                MONTH(registered_at) as month,
                SUM(payment_amount) as revenue,
                COUNT(*) as paid_registrations
            ')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => \Carbon\Carbon::create($item->year, $item->month)->format('M Y'),
                    'revenue' => $item->revenue,
                    'paid_registrations' => $item->paid_registrations
                ];
            });
    }

    /**
     * Create audit log entry
     */
    private function createAuditLog(Registration $registration, string $action, ?string $notes = null)
    {
        DB::table('registration_audit_logs')->insert([
            'registration_id' => $registration->id,
            'admin_user_id' => Auth::id(),
            'action' => $action,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }
}