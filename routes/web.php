    <?php
    use App\Http\Controllers\LoginController;
    use App\Http\Controllers\RegisterController;
    use App\Http\Controllers\LogoutController;
    use App\Http\Controllers\ItineraryController;
    use App\Http\Controllers\Auth\ForgotPasswordController;
    use App\Http\Controllers\Auth\ResetPasswordController;


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

    // Index route after login
    Route::get('/index', function () {
        return view('index');
    })->name('index')->middleware('auth');

    // Forgot Password
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    // Reset Password
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');


    Route::get('/commuting-guide', function () {
        return view('commuting-guide'); // Ensure 'commuting-guide.blade.php' exists in 'resources/views'
    })->name('commuting.guide')->middleware('auth');

    Route::view('/commuting-guide', 'commuting-guide');
    Route::post('/api/commute-guide', [App\Http\Controllers\ItineraryController::class, 'generateCommuteGuide']);
Route::middleware(['auth'])->group(function () {
    Route::post('/api/save-itinerary', [App\Http\Controllers\ItineraryController::class, 'saveItinerary']);
});