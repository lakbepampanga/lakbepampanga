<?php
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\SavedItineraryController;
use App\Http\Controllers\AdminController;


Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/routes', [AdminController::class, 'routes'])->name('routes.index');
});

Route::prefix('admin')->name('admin.')->group(function () {
    // User routes
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    
    // Route routes
    Route::get('/routes/{route}/edit', [AdminController::class, 'editRoute'])->name('routes.edit');
    Route::put('/routes/{route}', [AdminController::class, 'updateRoute'])->name('routes.update');
    
    // Stop routes
    Route::get('/routes/{route}/stops', [AdminController::class, 'stops'])->name('routes.stops');
    Route::get('/routes/{route}/stops/create', [AdminController::class, 'createStop'])->name('routes.stops.create');
    Route::post('/stops', [AdminController::class, 'storeStop'])->name('stops.store');
    Route::get('/stops/{stop}/edit', [AdminController::class, 'editStop'])->name('stops.edit');
    Route::put('/stops/{stop}', [AdminController::class, 'updateStop'])->name('stops.update');
    Route::delete('/stops/{stop}', [AdminController::class, 'deleteStop'])->name('stops.delete');
});
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


Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {  // Added prefix('admin')
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // Destinations Management
    Route::get('/destinations', [AdminController::class, 'destinations'])->name('admin.destinations.index'); // Added admin. prefix
    Route::get('/destinations/create', [AdminController::class, 'createDestination'])->name('admin.destinations.create');
    Route::post('/destinations', [AdminController::class, 'storeDestination'])->name('admin.destinations.store');
    Route::get('/destinations/{destination}/edit', [AdminController::class, 'editDestination'])->name('admin.destinations.edit');
    Route::put('/destinations/{destination}', [AdminController::class, 'updateDestination'])->name('admin.destinations.update');
    Route::delete('/destinations/{destination}', [AdminController::class, 'deleteDestination'])->name('admin.destinations.delete');
    
    // Jeepney Routes Management
    Route::get('/routes', [AdminController::class, 'routes'])->name('admin.routes.index'); // Added admin. prefix
    Route::get('/routes/create', [AdminController::class, 'createRoute'])->name('admin.routes.create');
    Route::post('/routes', [AdminController::class, 'storeRoute'])->name('admin.routes.store');
    Route::get('/routes/{route}/edit', [AdminController::class, 'editRoute'])->name('admin.routes.edit');
    Route::put('/routes/{route}', [AdminController::class, 'updateRoute'])->name('admin.routes.update');
    Route::delete('/routes/{route}', [AdminController::class, 'deleteRoute'])->name('admin.routes.delete');
    
    // Jeepney Stops Management
    Route::get('/routes/{route}/stops', [AdminController::class, 'stops'])->name('admin.routes.stops'); // Added admin. prefix
    Route::get('/routes/{route}/stops/create', [AdminController::class, 'createStop'])->name('admin.stops.create');
    Route::post('/routes/{route}/stops', [AdminController::class, 'storeStop'])->name('admin.stops.store');
    Route::get('/stops/{stop}/edit', [AdminController::class, 'editStop'])->name('admin.stops.edit');
    Route::put('/stops/{stop}', [AdminController::class, 'updateStop'])->name('admin.stops.update');
    Route::delete('/stops/{stop}', [AdminController::class, 'deleteStop'])->name('admin.stops.delete');
    
    // Users Management
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users.index'); // Added admin. prefix
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');


});
