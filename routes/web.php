<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HelloController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FestController;
use App\Http\Controllers\PrayerTimeController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;

use Illuminate\Support\Facades\Route;
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/hello', [HelloController::class, 'show']);
Route::get('/contact', [ContactController::class, 'create'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('rate_limit:contact_form,5,15')
    ->name('contact.store');

Route::resource('events', EventController::class);
Route::resource('blogs', BlogController::class);
Route::resource('fests', FestController::class);

// Gallery routes
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{type}/{id}', [GalleryController::class, 'show'])->name('gallery.show');
Route::get('/gallery/image-data/{image}', [GalleryController::class, 'getImageData'])->name('gallery.image-data');
Route::get('/gallery/widget', [GalleryController::class, 'widget'])->name('gallery.widget');

// Admin gallery routes
Route::middleware(['auth', 'can:create,App\Models\GalleryImage'])->group(function () {
    Route::get('/gallery/upload', [GalleryController::class, 'create'])->name('gallery.create');
    Route::post('/gallery', [GalleryController::class, 'store'])
        ->middleware('rate_limit:file_upload,10,5')
        ->name('gallery.store');
    Route::get('/gallery/{image}/edit', [GalleryController::class, 'edit'])->name('gallery.edit');
    Route::put('/gallery/{image}', [GalleryController::class, 'update'])->name('gallery.update');
    Route::delete('/gallery/{image}', [GalleryController::class, 'destroy'])->name('gallery.destroy');
    Route::delete('/gallery/bulk-delete', [GalleryController::class, 'bulkDelete'])->name('gallery.bulk-delete');
});

// Prayer Times routes
Route::get('/prayer-times', [PrayerTimeController::class, 'index'])->name('prayer-times.index');
Route::get('/prayer-times/{date}', [PrayerTimeController::class, 'show'])->name('prayer-times.show');
Route::get('/api/prayer-times/widget', [PrayerTimeController::class, 'widget'])->name('prayer-times.widget');

// Registration routes
Route::middleware('auth')->group(function () {
    // Individual registration routes
    Route::get('/events/{event}/register/individual', [App\Http\Controllers\RegistrationController::class, 'showIndividualForm'])->name('registrations.individual');
    Route::post('/events/{event}/register/individual', [App\Http\Controllers\RegistrationController::class, 'registerIndividual'])
        ->middleware('rate_limit:registration,3,60')
        ->name('registrations.individual.store');
    
    // Team registration routes
    Route::get('/events/{event}/register/team', [App\Http\Controllers\RegistrationController::class, 'showTeamForm'])->name('registrations.team');
    Route::post('/events/{event}/register/team', [App\Http\Controllers\RegistrationController::class, 'registerTeam'])
        ->middleware('rate_limit:registration,3,60')
        ->name('registrations.team.store');
    
    // Team management routes
    Route::get('/registrations/{registration}/manage-team', [App\Http\Controllers\RegistrationController::class, 'manageTeam'])->name('registrations.team.manage');
    Route::post('/registrations/{registration}/add-member', [App\Http\Controllers\RegistrationController::class, 'addTeamMember'])->name('registrations.team.add-member');
    Route::post('/registrations/{registration}/remove-member', [App\Http\Controllers\RegistrationController::class, 'removeTeamMember'])->name('registrations.team.remove-member');
    
    // Team invitation routes
    Route::post('/registrations/{registration}/accept-invitation', [App\Http\Controllers\RegistrationController::class, 'acceptTeamInvitation'])->name('registrations.accept-invitation');
    Route::post('/registrations/{registration}/decline-invitation', [App\Http\Controllers\RegistrationController::class, 'declineTeamInvitation'])->name('registrations.decline-invitation');
    
    // Registration management routes
    Route::get('/registrations/{registration}', [App\Http\Controllers\RegistrationController::class, 'show'])->name('registrations.show');
    Route::get('/registrations/{registration}/confirmation', [App\Http\Controllers\RegistrationController::class, 'confirmation'])->name('registrations.confirmation');
    Route::patch('/registrations/{registration}/cancel', [App\Http\Controllers\RegistrationController::class, 'cancel'])->name('registrations.cancel');
    Route::get('/my-registrations', [App\Http\Controllers\RegistrationController::class, 'myRegistrations'])->name('registrations.history');
    
    // Payment resubmission routes
    Route::get('/registrations/{registration}/payment/resubmit', [App\Http\Controllers\RegistrationController::class, 'showPaymentResubmissionForm'])->name('registrations.payment.resubmit');
    Route::post('/registrations/{registration}/payment/resubmit', [App\Http\Controllers\RegistrationController::class, 'resubmitPayment'])
        ->middleware('rate_limit:payment_submission,3,30')
        ->name('registrations.payment.resubmit.store');
    Route::get('/registrations/{registration}/payment/history', [App\Http\Controllers\RegistrationController::class, 'paymentHistory'])->name('registrations.payment.history');
});

// Admin Prayer Times routes
Route::middleware(['auth', 'can:manage-prayer-times'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/prayer-times', [PrayerTimeController::class, 'admin'])->name('prayer-times.index');
    Route::get('/prayer-times/edit/{date?}', [PrayerTimeController::class, 'edit'])->name('prayer-times.edit');
    Route::put('/prayer-times', [PrayerTimeController::class, 'update'])->name('prayer-times.update');
    Route::delete('/prayer-times/{date}', [PrayerTimeController::class, 'destroy'])->name('prayer-times.destroy');
    Route::get('/prayer-times/bulk-edit', [PrayerTimeController::class, 'bulkEdit'])->name('prayer-times.bulk-edit');
    Route::put('/prayer-times/bulk-update', [PrayerTimeController::class, 'bulkUpdate'])->name('prayer-times.bulk-update');
    Route::get('/prayer-times/history', [PrayerTimeController::class, 'history'])->name('prayer-times.history');
});

// Admin routes
Route::middleware(['auth', 'role:super_admin,event_admin'])->prefix('admin')->name('admin.')->group(function () {
    // Registration management
    Route::get('/registrations', [App\Http\Controllers\AdminRegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/analytics', [App\Http\Controllers\AdminRegistrationController::class, 'analytics'])->name('registrations.analytics');
    Route::get('/registrations/{registration}', [App\Http\Controllers\AdminRegistrationController::class, 'show'])->name('registrations.show');
    Route::get('/registrations/payment/verification', [App\Http\Controllers\AdminRegistrationController::class, 'paymentVerification'])->name('registrations.payment-verification');
    
    // Payment verification actions
    Route::post('/registrations/{registration}/approve-payment', [App\Http\Controllers\AdminRegistrationController::class, 'approvePayment'])->name('registrations.approve-payment');
    Route::post('/registrations/{registration}/reject-payment', [App\Http\Controllers\AdminRegistrationController::class, 'rejectPayment'])->name('registrations.reject-payment');
    Route::post('/registrations/bulk-approve-payments', [App\Http\Controllers\AdminRegistrationController::class, 'bulkApprovePayments'])->name('registrations.bulk-approve-payments');
    
    // Registration approval actions
    Route::post('/registrations/{registration}/approve', [App\Http\Controllers\AdminRegistrationController::class, 'approveRegistration'])->name('registrations.approve');
    Route::post('/registrations/{registration}/reject', [App\Http\Controllers\AdminRegistrationController::class, 'rejectRegistration'])->name('registrations.reject');
    
    // Bulk registration actions
    Route::post('/registrations/bulk-approve', [App\Http\Controllers\AdminRegistrationController::class, 'bulkApproveRegistrations'])->name('registrations.bulk-approve');
    Route::post('/registrations/bulk-reject', [App\Http\Controllers\AdminRegistrationController::class, 'bulkRejectRegistrations'])->name('registrations.bulk-reject');
    
    // Export functionality
    Route::get('/registrations/export', [App\Http\Controllers\AdminRegistrationController::class, 'export'])->name('registrations.export');
    Route::get('/events/{event}/registrations/export', [App\Http\Controllers\AdminRegistrationController::class, 'export'])->name('events.registrations.export');
});

// Super Admin routes
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminController::class, 'userManagement'])->name('user-management');
    Route::patch('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.update-role');
    Route::patch('/users/{user}/password', [AdminController::class, 'resetUserPassword'])->name('users.reset-password');
    
    // System Analytics
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    
    // Activity Logs
    Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity-logs');
    
    // System Settings
    Route::get('/settings', [AdminController::class, 'systemSettings'])->name('system-settings');
    Route::patch('/settings', [AdminController::class, 'updateSystemSettings'])->name('system-settings.update');
    
    // Data Export
    Route::get('/export', [AdminController::class, 'exportData'])->name('export-data');
});

// Test route for admin access (remove in production)
Route::middleware(['auth'])->get('/test-admin', function () {
    $user = auth()->user();
    return response()->json([
        'user' => $user->only(['name', 'email', 'role']),
        'is_admin' => $user->isAdmin(),
        'is_super_admin' => $user->isSuperAdmin(),
        'can_manage_events' => $user->canManageEvents(),
        'can_manage_content' => $user->canManageContent(),
        'gates' => [
            'access-admin' => auth()->user()->can('access-admin'),
            'manage-users' => auth()->user()->can('manage-users'),
            'manage-events' => auth()->user()->can('manage-events'),
            'manage-content' => auth()->user()->can('manage-content'),
        ]
    ]);
});

// Route::get('/blogs', [BlogController::class, 'index'])->name('index');
require __DIR__.'/auth.php';

