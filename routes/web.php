<?php
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\SavedItineraryController;


// Home page with combined login and register forms
Route::get('/', function () {
    return view('home'); // Show the home.blade.php file
})->name('home');

// Login route
Route::post('/login', [LoginController::class, 'login'])->name('login');

// Registration route
Route::post('/register', [RegisterController::class, 'register'])->name('register');

// Logout route
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Index route after login (auth middleware ensures user is logged in)
Route::get('/index', function () {
    return view('index');
})->name('index')->middleware('auth'); // Apply 'auth' middleware here

// Forgot Password
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

// Reset Password
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Commuting Guide route (auth middleware ensures user is logged in)
Route::get('/commuting-guide', function () {
    if (!auth()->check()) {
        return redirect()->route('home');  // Redirect to home page if user is not logged in
    }
    return view('commuting-guide'); // Show the commuting-guide page if authenticated
})->name('commuting.guide');

// API route for generating the commute guide (auth middleware ensures user is logged in)
Route::post('/api/commute-guide', [ItineraryController::class, 'generateCommuteGuide'])->middleware('auth');

// Save itinerary route (auth middleware ensures user is logged in)
Route::middleware(['auth'])->group(function () {
    Route::post('/api/save-itinerary', [ItineraryController::class, 'saveItinerary']);
});

// In routes/web.php



Route::middleware(['auth'])->group(function () {
    Route::get('/saved-itinerary', [SavedItineraryController::class, 'index'])->name('saved-itinerary');
    Route::delete('/itineraries/{itinerary}', [SavedItineraryController::class, 'destroy'])->name('itineraries.destroy');
});
