<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lakbe Pampanga</title>
   
     <!-- Favicons -->
     <link href="{{ asset('img/lakbe2.png') }}" rel="icon">
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
.full-description {
    transition: opacity 0.3s ease;
    cursor: pointer;
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
    <section class="mb-5 mt-5 bg-white" id="visited">
    <h2 class="fw-bold text-center mb-2">Most Visited Places</h2>
    <p class="text-center text-muted mb-4">Slide through Pampanga's top destinations and find your next adventure.</p>
    
    <div class="slider-container d-flex overflow-auto gap-4 px-3">
        @foreach($destinationStats->take(4) as $destination)
            <div class="place-card text-white position-relative rounded overflow-hidden flex-shrink-0" style="width: 22rem; height: 18rem;">
                @if($destination['image'])
                    <img src="{{ asset('storage/' . $destination['image']) }}" 
                         class="img-fluid w-100 h-100 object-fit-cover" 
                         alt="{{ $destination['name'] }}">
                @else
                    <img src="{{ asset('img/cards/default.webp') }}" 
                         class="img-fluid w-100 h-100 object-fit-cover" 
                         alt="{{ $destination['name'] }}">
                @endif
                <div class="position-absolute bottom-0 start-0 p-3 bg-opacity-50 bg-dark w-100">
                    <h5 class="fw-bold mb-0">{{ $destination['name'] }}, Philippines</h5>
                    <small class="text-white-50">{{ Str::limit($destination['description'], 50) }} 
                        <a href="javascript:void(0)" class="text-white read-more">Read more</a>
                    </small>
                </div>
                <div class="full-description position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-none">
                    <div class="p-4 text-white h-100 overflow-auto">
                        <h5 class="fw-bold mb-3">{{ $destination['name'] }}, Philippines</h5>
                        <p>{{ $destination['description'] }}</p>
                        <small class="position-absolute bottom-0 end-0 p-3">Click anywhere to close</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
<section class="mb-5 bg-white p-4 rounded" id="itineraries">
    <div class="row g-5">
        <!-- Saved Itineraries -->
        <div class="col-md-12 d-flex flex-column">
            <h3 class="fw-bold text-center mb-2">Saved Itineraries</h3>
            <div class="saved-itineraries-slider d-flex gap-3 overflow-auto p-3 rounded shadow-sm flex-grow-1">
                @if($savedItineraries->isEmpty())
                    <div class="itinerary-card card rounded shadow-sm flex-shrink-0" style="min-width: 24rem; height: 15rem;">
                        <div class="card-body d-flex flex-column justify-content-center align-items-center">
                            <h4 class="card-title fw-bold">No Itineraries Yet</h4>
                            <p class="card-text text-muted">Start planning your adventure today!</p>
                            <a href="{{ route('index') }}" class="btn btn-outline-primary btn-lg rounded-pill mt-2">Create Itinerary</a>
                        </div>
                    </div>
                @else
                    @foreach($savedItineraries as $itinerary)
                        <div class="itinerary-card card rounded shadow-sm flex-shrink-0" style="min-width: 24rem; height: 15rem;">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                                <h4 class="card-title fw-bold">{{ $itinerary->name }}</h4>
                                <p class="card-text text-muted">
                                    {{ count($itinerary->itinerary_data) }} destinations • {{ $itinerary->duration_hours }} hours
                                </p>
                                <a href="{{ route('saved-itinerary') }}" class="btn btn-outline-primary btn-lg rounded-pill mt-2">View Itinerary</a>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('saved-itinerary') }}" class="btn btn-custom rounded-pill">See All Itineraries</a>
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

function showFullDescription(event, name, description) {
    event.preventDefault();
    const card = event.target.closest('.place-card');
    const fullDescription = card.querySelector('.full-description');
    fullDescription.style.display = 'block';
}

function hideDescription(event, element) {
    event.stopPropagation();
    element.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    // Add click listeners to all "Read more" links
    document.querySelectorAll('.read-more').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.place-card');
            const fullDescription = card.querySelector('.full-description');
            fullDescription.classList.remove('d-none');
        });
    });

    // Add click listeners to all full description divs
    document.querySelectorAll('.full-description').forEach(div => {
        div.addEventListener('click', function() {
            this.classList.add('d-none');
        });
    });
});

    </script>

</body>
