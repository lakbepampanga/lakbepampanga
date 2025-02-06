<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lakbe Pampanga</title>
   
     <!-- Favicons -->
  <link href="{{ asset('img/favicon.png') }}" rel="icon">
<link href="{{ asset('img/apple-touch-icon.png') }}" rel="apple-touch-icon">


  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect"   crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&display=swap" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

<!-- Main CSS File -->
<link href="{{ asset('css/main2.css') }}" rel="stylesheet">


  <!-- Bootstrap CSS and JS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
    <style>
     
        
.btn-custom{
    background-color: var(--button-color); /* Desired background color */
    color: var(--button-text-color);
}

.btn-custom:hover{
    background-color: var(--button-hover-color);
    color: white;
    transition: 0.3s;   /* Hover border color */
}


</style>

</head>

<body class="index-page">

<header id="header" class="header d-flex bg-white fixed-top align-items-center">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

        <!-- <a href="/" class="logo d-flex align-items-center">
            <h1 class="sitename">Lakbe Pampanga</h1>
        </a> -->

        <a href="/" class="logo d-flex align-items-center">
            <img src="{{ asset('img/lakbe-logo1.png') }}" alt="Lakbe Pampanga Logo" class="img-fluid">
        </a>


        <nav id="navmenu" class="navmenu">
        <ul>
    <li><a href="/user-home" class="{{ request()->is('user-home') ? 'active' : '' }}">Home</a></li>
    <li><a href="/index" class="{{ request()->is('index') ? 'active' : '' }}">Plan</a></li>
    <li><a href="/saved-itinerary" class="{{ request()->is('saved-itinerary') ? 'active' : '' }}">Saved Itineraries</a></li>
    <li><a href="/commuting-guide" class="{{ request()->is('commuting-guide') ? 'active' : '' }}">Commuting Guide</a></li>
    <li>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-custom rounded-pill btn-md px-3 py-2">Logout</button>
        </form>
    </li>
</ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

    </div>
</header>

<main class="main container mt-5">
    <!-- Welcome Section -->
    <section class="text-center mb-5">
        <h1 class="fw-bold">Welcome Back, {{ Auth::user()->name }}!</h1>
        <p>Let’s plan your next adventure in Pampanga’s First District.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="/index" class="btn btn-primary btn-lg rounded-pill">Plan a New Itinerary</a>
            <a href="/saved-itinerary" class="btn btn-outline-secondary btn-lg rounded-pill">View Saved Itineraries</a>
        </div>
    </section>

    <!-- Quick Action Section -->
    <section class="mb-5">
        <h2 class="fw-bold text-center mb-4">Quick Actions</h2>
        <div class="row text-center">
            <div class="col-md-4">
                <a href="/index" class="btn btn-custom rounded-pill px-4 py-3">
                    <i class="bi bi-plus-circle me-2"></i> Create New Itinerary
                </a>
            </div>
            <div class="col-md-4">
                <a href="/commuting-guide" class="btn btn-custom rounded-pill px-4 py-3">
                    <i class="bi bi-bus-front me-2"></i> Commuting Guide
                </a>
            </div>
            <div class="col-md-4">
                <a href="/saved-itinerary" class="btn btn-custom rounded-pill px-4 py-3">
                    <i class="bi bi-bookmark-star me-2"></i> Saved Itineraries
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Destinations -->
    <section class="mb-5">
        <h2 class="fw-bold text-center mb-4">Featured Destinations</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <img src="{{ asset('img/cards/angeles.webp') }}" class="card-img-top" alt="Angeles">
                    <div class="card-body">
                        <h5 class="card-title">Angeles, Pampanga</h5>
                        <p class="card-text">Explore the vibrant streets and historical landmarks of Angeles City.</p>
                        <a href="/destinations/angeles" class="btn btn-primary btn-sm">Discover More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <img src="{{ asset('img/cards/magalang.webp') }}" class="card-img-top" alt="Magalang">
                    <div class="card-body">
                        <h5 class="card-title">Magalang, Pampanga</h5>
                        <p class="card-text">Immerse yourself in nature at Mount Arayat and other scenic spots.</p>
                        <a href="/destinations/magalang" class="btn btn-primary btn-sm">Discover More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <img src="{{ asset('img/cards/mabalacat.webp') }}" class="card-img-top" alt="Mabalacat">
                    <div class="card-body">
                        <h5 class="card-title">Mabalacat, Pampanga</h5>
                        <p class="card-text">Experience the gateway to Clark and modern Pampanga.</p>
                        <a href="/destinations/mabalacat" class="btn btn-primary btn-sm">Discover More</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Commute Guide -->
    <section class="mb-5">
        <h2 class="fw-bold text-center mb-4">Your Commute Guide</h2>
        <div class="text-center">
            <p>Navigate Pampanga with ease. Check jeepney routes, schedules, and fares.</p>
            <a href="/commuting-guide" class="btn btn-outline-primary rounded-pill">Explore Commute Guide</a>
        </div>
    </section>
</main>


</body>
