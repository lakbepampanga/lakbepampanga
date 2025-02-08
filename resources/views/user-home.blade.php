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
    <!-- Most Visited Places -->
    <section class="mb-5 mt-5 bg-white " id="visited">
    <h2 class="fw-bold text-center mb-2">Most Visited Places</h2>
    <p class="text-center text-muted mb-4">Slide through Pampanga’s top destinations and find your next adventure.</p>
    
    <div class="slider-container d-flex overflow-auto gap-4 px-3">
        <div class="place-card text-white position-relative rounded overflow-hidden flex-shrink-0" style="width: 22rem; height: 18rem;">
            <img src="{{ asset('img/cards/angeles.webp') }}" class="img-fluid w-100 h-100 object-fit-cover" alt="Angeles">
            <div class="position-absolute bottom-0 start-0 p-3 bg-opacity-50 bg-dark w-100">
                <h5 class="fw-bold mb-0">Angeles, Philippines</h5>
            </div>
        </div>
        <div class="place-card text-white position-relative rounded overflow-hidden flex-shrink-0" style="width: 22rem; height: 18rem;">
            <img src="{{ asset('img/cards/magalang.webp') }}" class="img-fluid w-100 h-100 object-fit-cover" alt="Magalang">
            <div class="position-absolute bottom-0 start-0 p-3 bg-opacity-50 bg-dark w-100">
                <h5 class="fw-bold mb-0">Magalang, Philippines</h5>
            </div>
        </div>
        <div class="place-card text-white position-relative rounded overflow-hidden flex-shrink-0" style="width: 22rem; height: 18rem;">
            <img src="{{ asset('img/cards/mabalacat.webp') }}" class="img-fluid w-100 h-100 object-fit-cover" alt="Mabalacat">
            <div class="position-absolute bottom-0 start-0 p-3 bg-opacity-50 bg-dark w-100">
                <h5 class="fw-bold mb-0">Mabalacat, Philippines</h5>
            </div>
        </div>
        <div class="place-card text-white position-relative rounded overflow-hidden flex-shrink-0" style="width: 22rem; height: 18rem;">
            <img src="{{ asset('img/cards/mabalacat.webp') }}" class="img-fluid w-100 h-100 object-fit-cover" alt="Clark">
            <div class="position-absolute bottom-0 start-0 p-3 bg-opacity-50 bg-dark w-100">
                <h5 class="fw-bold mb-0">Coffee Cat, Angeles</h5>
            </div>
        </div>
    </div>
</section>

<section class="mb-5 bg-white p-4 rounded" id="itineraries">
    <div class="row g-5">
        <!-- Saved Itineraries -->
        <div class="col-md-12 d-flex flex-column">
            <h3 class="fw-bold text-center mb-2">Saved Itineraries</h3>
            <div class="saved-itineraries-slider d-flex gap-3 overflow-auto p-3 rounded shadow-sm flex-grow-1">
                <div class="itinerary-card card rounded shadow-sm flex-shrink-0" style="min-width: 24rem; height: 15rem;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <h4 class="card-title fw-bold">Adventure in Angeles</h4>
                        <p class="card-text text-muted">Explore Angeles City with this 1-day itinerary.</p>
                        <a href="/saved-itinerary" class="btn btn-outline-primary btn-lg rounded-pill mt-2">View Itinerary</a>
                    </div>
                </div>
                <div class="itinerary-card card rounded shadow-sm flex-shrink-0" style="min-width: 24rem; height: 15rem;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <h4 class="card-title fw-bold">Magalang Escape</h4>
                        <p class="card-text text-muted">Enjoy the scenic beauty of Mount Arayat.</p>
                        <a href="/saved-itinerary" class="btn btn-outline-primary btn-lg rounded-pill mt-2">View Itinerary</a>
                    </div>
                </div>
                <div class="itinerary-card card rounded shadow-sm flex-shrink-0" style="min-width: 24rem; height: 15rem;">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <h4 class="card-title fw-bold">Clark Gateway</h4>
                        <p class="card-text text-muted">Discover Clark's top attractions in a day.</p>
                        <a href="/saved-itinerary" class="btn btn-outline-primary btn-lg rounded-pill mt-2">View Itinerary</a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="/saved-itinerary" class="btn btn-custom rounded-pill">See All Itineraries</a>
            </div>
        </div>
    </div>
</section>





</main>

<a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<!-- main js -->
<script src="{{ asset('js/main.js') }}"></script>

<!-- Vendor JS Files -->
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/php-email-form/validate.js') }}"></script>
<script src="{{ asset('vendor/aos/aos.js') }}"></script>
<script src="{{ asset('vendor/glightbox/js/glightbox.min.js') }}"></script>
<script src="{{ asset('vendor/purecounter/purecounter_vanilla.js') }}"></script>
<script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>

<script>
    const slider = document.querySelector('.slider-container');

    let isDown = false;
    let startX;
    let scrollLeft;

    slider.addEventListener('mousedown', (e) => {
        isDown = true;
        slider.classList.add('active');
        startX = e.pageX - slider.offsetLeft;
        scrollLeft = slider.scrollLeft;
    });

    slider.addEventListener('mouseleave', () => {
        isDown = false;
        slider.classList.remove('active');
    });

    slider.addEventListener('mouseup', () => {
        isDown = false;
        slider.classList.remove('active');
    });

    slider.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const walk = (x - startX) * 2; // Scroll-fast
        slider.scrollLeft = scrollLeft - walk;
    });
</script>

<!-- saved itinerary slider -->
<script>
    const itinerarySlider = document.querySelector('.saved-itineraries-slider');

let isDown = false;
let startX;
let scrollLeft;

itinerarySlider.addEventListener('mousedown', (e) => {
    isDown = true;
    itinerarySlider.classList.add('active');
    startX = e.pageX - itinerarySlider.offsetLeft;
    scrollLeft = itinerarySlider.scrollLeft;
});

itinerarySlider.addEventListener('mouseleave', () => {
    isDown = false;
    itinerarySlider.classList.remove('active');
});

itinerarySlider.addEventListener('mouseup', () => {
    isDown = false;
    itinerarySlider.classList.remove('active');
});

itinerarySlider.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - itinerarySlider.offsetLeft;
    const walk = (x - startX) * 2; // Adjust scroll speed
    itinerarySlider.scrollLeft = scrollLeft - walk;
});


    </script>

</body>
