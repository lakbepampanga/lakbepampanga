<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\SavedItineraryController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\DestinationVisitController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CommutingReportController;
use App\Http\Controllers\HomeController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Public routes
Route::get('/admin/cities/list', [App\Http\Controllers\CityController::class, 'list'])->name('admin.cities.list');
Route::post('/cities', [App\Http\Controllers\CityController::class, 'store'])->name('cities.store');
Route::get('/', function () {
    return view('home');
})->name('home');

// Authentication routes
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/register', [RegisterController::class, 'register'])->name('register');
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Email Verification Routes
Route::get('/email/verify', function () {
    if (auth()->user()->hasVerifiedEmail()) {
        return redirect('/')->with('success', 'Email already verified!');
    }
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    try {
        \Log::info('Email verification attempt for user: ' . auth()->id());
        
        $request->fulfill();
        
        // Explicitly update the user's email_verified_at timestamp
        $user = auth()->user();
        $user->email_verified_at = now();
        $user->save();
        
        \Log::info('Email verified successfully for user: ' . $user->id);
        
        // Redirect to home page with success message
        return redirect('/')->with('success', 'Email verified successfully! You can now log in.');
        
    } catch (\Exception $e) {
        \Log::error('Email verification error: ' . $e->getMessage());
        return redirect('/')->with('error', 'Email verification failed: ' . $e->getMessage());
    }
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    try {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Verification link sent! Please check your email.');
    } catch (\Exception $e) {
        \Log::error('Error sending verification notification: ' . $e->getMessage());
        return back()->with('error', 'Failed to send verification email. Please try again.');
    }
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Add a resend verification email route
Route::get('/email/resend', function (Request $request) {
    try {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/')->with('success', 'Email already verified!');
        }

        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Verification link sent! Please check your email.');
    } catch (\Exception $e) {
        \Log::error('Error resending verification email: ' . $e->getMessage());
        return back()->with('error', 'Failed to resend verification email. Please try again.');
    }
})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');

// Password Reset Routes
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Protected routes (require authentication and email verification)
Route::middleware(['auth', 'verified'])->group(function () {
    // Itinerary-Gen
    Route::get('/index', function () {
        return view('index');
    })->name('index');

    // User-Homepage
    Route::get('/user-home', [HomeController::class, 'index'])->name('user-home');

    // Commuting Guide
    Route::get('/commuting-guide', function () {
        return view('commuting-guide');
    })->name('commuting.guide');
    
    // Itinerary routes
    Route::post('/api/commute-guide', [ItineraryController::class, 'generateCommuteGuide']);
    Route::post('/api/save-itinerary', [ItineraryController::class, 'saveItinerary']);
    Route::post('/itineraries/{itinerary}/complete', [ItineraryController::class, 'complete'])
        ->name('itineraries.complete');
    
    // Saved Itineraries
    Route::get('/saved-itinerary', [SavedItineraryController::class, 'index'])->name('saved-itinerary');
    Route::put('/itineraries/{id}', [SavedItineraryController::class, 'update'])->name('itineraries.update');
    Route::delete('/itineraries/{itinerary}', [SavedItineraryController::class, 'destroy'])->name('itineraries.destroy');
    
    // Destination visits
    Route::post('/destinations/mark-visited', [DestinationVisitController::class, 'markVisited'])
        ->name('destinations.markVisited');

    // User Reports (Itinerary Reports)
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    
    // User Commuting Reports
    Route::post('/commuting-reports', [CommutingReportController::class, 'store'])
        ->name('commuting.reports.store');
});

// Admin routes (require authentication, verification, and admin role)
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Destinations Management
    Route::get('/destinations', [AdminController::class, 'destinations'])->name('destinations.index');
    Route::get('/destinations/create', [AdminController::class, 'createDestination'])->name('destinations.create');
    Route::post('/destinations', [AdminController::class, 'storeDestination'])->name('destinations.store');
    Route::get('/destinations/{destination}/edit', [AdminController::class, 'editDestination'])->name('destinations.edit');
    Route::put('/destinations/{destination}', [AdminController::class, 'updateDestination'])->name('destinations.update');
    Route::delete('/destinations/{destination}', [AdminController::class, 'deleteDestination'])->name('destinations.delete');
    
    // Routes Management
    Route::get('/routes', [AdminController::class, 'routes'])->name('routes.index');
    Route::get('/routes/create', [AdminController::class, 'createRoute'])->name('routes.create');
    Route::post('/routes', [AdminController::class, 'storeRoute'])->name('routes.store');
    Route::get('/routes/{route}/edit', [AdminController::class, 'editRoute'])->name('routes.edit');
    Route::put('/routes/{route}', [AdminController::class, 'updateRoute'])->name('routes.update');
    Route::delete('/routes/{route}', [AdminController::class, 'deleteRoute'])->name('routes.delete');
    
    // Stops Management
    Route::get('/routes/{route}/stops', [AdminController::class, 'stops'])->name('routes.stops');
    Route::get('/routes/{route}/stops/create', [AdminController::class, 'createStop'])->name('stops.create');
    Route::post('/routes/{route}/stops', [AdminController::class, 'storeStop'])->name('stops.store');
    Route::get('/stops/{stop}/edit', [AdminController::class, 'editStop'])->name('stops.edit');
    Route::put('/stops/{stop}', [AdminController::class, 'updateStop'])->name('stops.update');
    Route::delete('/stops/{stop}', [AdminController::class, 'deleteStop'])->name('stops.delete');
    
    // Users Management
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');

    // Reports Management
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::put('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');

    // Commuting Reports Management
    Route::put('/commuting-reports/{report}', [CommutingReportController::class, 'update'])
        ->name('commuting.reports.update');
});