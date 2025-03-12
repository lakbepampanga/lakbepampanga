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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
  
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

.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
}

.custom-toast {
    opacity: 0;
    transition: all 0.3s ease-in-out;
    background: white;
    border-radius: 4px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    position: relative;
}

.custom-toast.show {
    opacity: 1;
}

.toast-success {
    border-left: 4px solid #198754;
}

.toast-error {
    border-left: 4px solid #dc3545;
}

/* Enhanced button styles */
.btn-close {
    position: relative;
    padding: 0.5rem !important;
    margin: -0.5rem -0.5rem -0.5rem auto !important;
    cursor: pointer !important;
    background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
    border: 0;
    border-radius: 0.25rem;
    opacity: 0.5;
    width: 1rem;
    height: 1rem;
}

.btn-close:hover {
    opacity: 1 !important;
    background-color: rgba(0,0,0,0.1);
}

/* Make sure the button is clickable */
.btn-close {
    pointer-events: auto !important;
    user-select: none;
    -webkit-user-select: none;
}

/* Ensure the entire toast doesn't block button clicks */
.custom-toast * {
    pointer-events: auto;
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
    <li><a href="#about">About</a></li>
    <li><a href="#features">Features</a></li>
    <li><a href="#faq">FAQ</a></li>
    <button type="button" class="btn btn-custom rounded-pill btn-md px-3 py-2" 
            data-bs-toggle="modal" data-bs-target="#loginModal">
        Sign in
    </button>
</ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

    </div>
</header>

<!-- Flash Messages -->
@if(session('success'))
    <div class="alert alert-success text-center flash-message" id="success-message">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger flash-message" id="error-message">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- MODALS -->

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- close buttonss -->
        <div class="d-flex justify-content-end p-1">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- modal header -->
            <div class="modal-header d-flex flex-column align-items-center">
                <!-- Logo (Centered) -->
                <img src="{{ asset('img/lakbe-logo1.png') }}" alt="Lakbe Pampanga Logo" class="img-fluid" style="max-height: 100px;">

                <!-- Title (Below the Logo, Centered) -->
                <h5 class="modal-title mt-2 text-center" id="loginModalLabel">
                    Step Inside – Your Adventure Awaits!
                </h5>
            </div>


            <div class="modal-body">
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="text" id="email" name="email" class="form-control" placeholder="Enter your email or phone" required>
            @error('email')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            @error('password')
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <!-- Show Password & Forgot Password Aligned in One Row -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            
            <div>
                <p>
                <input type="checkbox" id="showPassword" onclick="togglePassword()"> Show Password
                </p>
            </div>
            <div>
                <p>
                <a href="#forgotPasswordModal" data-bs-toggle="modal" 
                data-bs-dismiss="modal">Forgot Password?</a>
                    </p>
            </div>
        </div>

        <!-- Login Button -->
        <button type="submit" class="btn btn-primary w-100">Login</button>

        <!-- Sign Up Link -->
        <div class="text-center mt-3">
        <p>    
        <span>Don't have an account yet? </span>
            <a href="#registerModal" data-bs-toggle="modal" data-bs-dismiss="modal">Sign up</a>
        </p>
        </div>
    </form>
</div>

        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Close button -->
            <div class="d-flex justify-content-end p-1">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Header with Logo -->
            <div class="modal-header d-flex flex-column align-items-center">
                <!-- Logo -->
                <img src="{{ asset('img/lakbe-logo1.png') }}" alt="Lakbe Pampanga Logo" class="img-fluid" style="max-height: 80px;">
                <!-- Title -->
                <h5 class="modal-title mt-2" id="registerModalLabel">Create Your Account</h5>
                <p class="text-muted small">Join us and start exploring Pampanga!</p>
            </div>

            <div class="modal-body">
                <form id="registerForm" method="POST" action="{{ route('register') }}" novalidate>
                    @csrf
                    <!-- Email -->
                    <div class="mb-3">
                        <label for="registerEmail" class="form-label">Email Address</label>
                        <input type="email" 
                               id="registerEmail" 
                               name="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               placeholder="Enter your email"
                               required>
                        <div class="invalid-feedback" id="emailError"></div>
                    </div>

                    <!-- Username -->
                    <div class="mb-3">
                        <label for="registerUsername" class="form-label">Username</label>
                        <input type="text" 
                               id="registerUsername" 
                               name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               placeholder="Choose a username"
                               required>
                        <div class="invalid-feedback" id="nameError"></div>
                    </div>

                    <!-- Gender -->
                    <div class="mb-3">
                        <label class="form-label d-block">Gender</label>
                        <div class="btn-group w-100" role="group" aria-label="Gender selection">
                            <input type="radio" class="btn-check" name="gender" id="male" value="male" required>
                            <label class="btn btn-outline-primary" for="male">
                                <i class="bi bi-gender-male me-1"></i>Male
                            </label>

                            <input type="radio" class="btn-check" name="gender" id="female" value="female" required>
                            <label class="btn btn-outline-primary" for="female">
                                <i class="bi bi-gender-female me-1"></i>Female
                            </label>
                        </div>
                        <div class="invalid-feedback" id="genderError"></div>
                    </div>

                    <!-- Age -->
                    <div class="mb-3">
                        <label for="age" class="form-label">Age</label>
                        <input type="number" 
                               id="age" 
                               name="age" 
                               class="form-control @error('age') is-invalid @enderror" 
                               placeholder="Enter your age"
                               min="1" 
                               max="120" 
                               required>
                        <div class="invalid-feedback" id="ageError"></div>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="registerPassword" class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" 
                                   id="registerPassword" 
                                   name="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   placeholder="Create a password"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleRegisterPassword()">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="passwordError"></div>
                        <small class="text-muted">Must be at least 8 characters long</small>
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" 
                                   id="confirmPassword" 
                                   name="password_confirmation" 
                                   class="form-control" 
                                   placeholder="Confirm your password"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" onclick="toggleConfirmPassword()">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback" id="passwordConfirmationError"></div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100 py-2 mt-3">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Create Account
                    </button>
                </form>

                <!-- Login Link -->
                <p class="mt-3 text-center">
                    Already have an account? 
                    <a href="#loginModal" 
                       data-bs-dismiss="modal" 
                       data-bs-toggle="modal" 
                       data-bs-target="#loginModal">
                        Sign in
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>


<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <!-- Modal Body -->
      <div class="modal-body">
        <form id="forgotPasswordForm" method="POST" action="{{ route('password.email') }}">
          @csrf
          <div class="mb-3">
            <label for="forgotEmail" class="form-label">Enter your email address:</label>
            <input type="email" id="forgotEmail" name="email" class="form-control" placeholder="Enter your registered email" required>
            @error('email')
                <small class="text-danger">{{ $message }}</small>
            @enderror
          </div>
          <button type="submit" class="btn btn-primary w-100">Submit</button>
        </form>
        <p class="mt-3 text-center">
          Remember your password? 
          <a href="#loginModal" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal">Login here</a>
        </p>
      </div>
    </div>
  </div>
</div>

<!-- end of modals -->

<!-- main start -->
<main class="main container mt-5 pt-5 mb-5">

    <!-- Hero Section -->
    <section class="hero text-center bg-white">
    <div>
        <h1 class="display-3 fw-bold">Lakbe Pampanga</h1>
        <p class="lead">Explore Pampanga’s First District with ease—find the best routes, top spots<br>and convenient jeepney rides.</p>

        <div class="text-center mt-4">
            <button class="btn btn-custom rounded-pill px-4 py-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                Start Trip
            </button>
        </div>
    </div>
</section>
    <!-- district 1 -->

    <section class="mt-5 bg-white" id="about">
    <h2 class="text-center fw-bold mb-4">District 1</h2>
    <!-- Informative Description -->
    <div class="row align-items-center mt-5 mb-5">
    <!-- Left Side: Image -->
    <div class="col-md-6 text-center">
        <img src="{{ asset('img/cards/district1.png') }}" alt="Pampanga's First District" class="img-fluid rounded">
    </div>

    <!-- Right Side: Text -->
    <div class="col-md-6 card-custom-body">
        <h3 class="fw-bold">Discover Pampanga's First District</h3>
        <p>
            Pampanga's First District is a treasure trove of cultural heritage, natural wonders, and vibrant attractions. 
            Home to Angeles City, Magalang, and Mabalacat, this district offers a blend of modern conveniences and historical charm.
        </p>
        <p>
            Whether you're exploring Angeles City's lively streets, enjoying the natural beauty of Magalang and Mount Arayat, 
            or experiencing the gateway to Clark Freeport Zone in Mabalacat, there's something for everyone here.
        </p>
        <div class="mt-3">
    <a href="#" class="btn btn-custom px-4 py-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#loginModal">
        Explore Now
    </a>
</div>

    </div>
</div>


    <div class="row">
        <!-- Angeles -->
        <div class="col-md-4">
            <div class="card destination-card" data-bs-toggle="modal" data-bs-target="#angelesModal">
                <img src="{{ asset('img/cards/angeles.webp') }}" class="card-img-top" alt="Angeles">
            </div>
            <h5 class="card-title text-center fw-bold">Angeles, Pampanga</h5>
        </div>

        <!-- Magalang -->
        <div class="col-md-4">
            <div class="card destination-card" data-bs-toggle="modal" data-bs-target="#magalangModal">
                <img src="{{ asset('img/cards/magalang.webp') }}" class="card-img-top" alt="Magalang">
            </div>
            <h5 class="card-title text-center fw-bold">Magalang, Pampanga</h5>
        </div>

        <!-- Mabalacat -->
        <div class="col-md-4">
            <div class="card destination-card" data-bs-toggle="modal" data-bs-target="#mabalacatModal">
                <img src="{{ asset('img/cards/mabalacat.webp') }}" class="card-img-top" alt="Mabalacat">
            </div>
            <h5 class="card-title text-center fw-bold">Mabalacat, Pampanga</h5>
        </div>
    </div>

</section>


<!-- Angeles Modal -->
<div class="modal fade" id="angelesModal" tabindex="-1" aria-labelledby="angelesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="angelesModalLabel">Angeles, Pampanga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('img/cards/angeles.webp') }}" class="img-fluid mb-3" alt="Angeles">
                <p>Angeles City is known for its rich history, vibrant nightlife, and famous landmarks such as the Holy Rosary Parish Church and the Salakot Arch.</p>
            </div>
        </div>
    </div>
</div>

<!-- Magalang Modal -->
<div class="modal fade" id="magalangModal" tabindex="-1" aria-labelledby="magalangModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="magalangModalLabel">Magalang, Pampanga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('img/cards/magalang.webp') }}" class="img-fluid mb-3" alt="Magalang">
                <p>Magalang is a peaceful town famous for its heritage sites, local delicacies, and Mount Arayat National Park, a must-visit for nature enthusiasts.</p>
            </div>
        </div>
    </div>
</div>

<!-- Mabalacat Modal -->
<div class="modal fade" id="mabalacatModal" tabindex="-1" aria-labelledby="mabalacatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mabalacatModalLabel">Mabalacat, Pampanga</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('img/cards/mabalacat.webp') }}" class="img-fluid mb-3" alt="Mabalacat">
                <p>Mabalacat is known for its role as the gateway to Clark Freeport Zone and its rich cultural heritage, offering a blend of modern and traditional attractions.</p>
            </div>
        </div>
    </div>
</div>


    <!-- features -->
    <section class="mt-1 text-center bg-white" id="features">
    <h2 class="fw-bold mb-4">How It Works</h2>
    <div class="row">
        <!-- Feature 1 -->
        <div class="col-md-4">
            <div class="card how-it-works-card border-0 shadow-sm p-3">
                <div class="mb-3">
                    <i class="bi bi-map-fill" style="font-size: 2rem;"></i>
                </div>
                <h4>Pick a Destination</h4>
                <p>Explore a curated list of attractions in Angeles, Mabalacat, and Magalang.</p>
            </div>
        </div>

        <!-- Feature 2 -->
        <div class="col-md-4">
            <div class="card how-it-works-card border-0 shadow-sm p-3">
                <div class="mb-3">
                    <i class="bi bi-compass-fill" style="font-size: 2rem;"></i>
                </div>
                <h4>Plan Your Route</h4>
                <p>Get the most convenient routes using jeepneys and public transportation.</p>
            </div>
        </div>

        <!-- Feature 3 -->
        <div class="col-md-4">
            <div class="card how-it-works-card border-0 shadow-sm p-3">
                <div class="mb-3">
                    <i class="bi bi-stars" style="font-size: 2rem;"></i>
                </div>
                <h4>Enjoy Your Trip</h4>
                <p>Follow your itinerary and experience the beauty of Pampanga with ease.</p>
            </div>
        </div>
    </div>
</section>

    <!-- Call to Action -->
    <section class="text-center mt-5 bg-white" id="faq">
    <h2 class="fw-bold mb-4">Frequently Asked Questions</h2>
    <div class="accordion" id="faqAccordion">
        <!-- Question 1 -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                    What is Lakbe Pampanga?
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Lakbe Pampanga is a travel itinerary planner designed to help you explore Pampanga's First District, including Angeles, Mabalacat, and Magalang. It provides destinations, routes, and transportation options.
                </div>
            </div>
        </div>

        <!-- Question 2 -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    How does the itinerary creation work?
                </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Simply select your desired destinations, and the app will generate a personalized itinerary with routes, transportation options, and travel tips to help you make the most of your trip.
                </div>
            </div>
        </div>

        <!-- Question 3 -->
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    Can I use Lakbe Pampanga for free?
                </button>
            </h2>
            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                    Yes, Lakbe Pampanga is free to use. You can explore destinations, create itineraries, and access transportation options without any cost.
                </div>
            </div>
        </div>
    </div>
</section>


    </main>

<footer id="footer" class="footer dark-background">

  <div class="container copyright text-center mt-4">
    <p>© <span>Copyright</span> <strong class="px-1 sitename">Lakbe Pampanga</strong> <span>All Rights Reserved</span></p>
    <div class="credits">
      <!-- All the links in the footer should remain intact. -->
      <!-- You can delete the links only if you've purchased the pro version. -->
      <!-- Licensing information: https://bootstrapmade.com/license/ -->
      <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
      Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a> Distributed By <a href="https://themewagon.com">ThemeWagon</a>
    </div>
  </div>

</footer>

<!-- Scroll Top -->
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
  document.addEventListener('hidden.bs.modal', function () {
  const backdrops = document.querySelectorAll('.modal-backdrop');
  backdrops.forEach(backdrop => backdrop.remove());
});

function togglePassword() {
        var passwordField = document.getElementById('password');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
        } else {
            passwordField.type = 'password';
        }
    }
    function toggleRegisterPassword() {
        var passwordField = document.getElementById('registerPassword');
        var confirmPasswordField = document.getElementById('confirmPassword');
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            confirmPasswordField.type = 'text';
        } else {
            passwordField.type = 'password';
            confirmPasswordField.type = 'password';
        }
    }

     // Automatically hide flash messages after 5 seconds
     setTimeout(function() {
        var successMessage = document.getElementById('success-message');
        var errorMessage = document.getElementById('error-message');
        
        if (successMessage) {
            successMessage.style.display = 'none';
        }

        if (errorMessage) {
            errorMessage.style.display = 'none';
        }
    }, 5000);


</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var scrollTopBtn = document.getElementById("scroll-top");

        // Show button when scrolling down
        window.addEventListener("scroll", function () {
            if (window.scrollY > 200) { // Show button after 200px scroll
                scrollTopBtn.style.display = "flex";
            } else {
                scrollTopBtn.style.display = "none";
            }
        });

        // Scroll to top when clicked
        scrollTopBtn.addEventListener("click", function (e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    });

    function showToast(message, type = 'success') {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = `custom-toast toast-${type} p-3 mb-2`;
    
    // Create the toast content
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="toast-body flex-grow-1">
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close ms-2" aria-label="Close" style="z-index: 2;"></button>
        </div>
    `;

    toastContainer.appendChild(toast);
    
    // Get the close button and add event listener
    const closeButton = toast.querySelector('.btn-close');
    if (closeButton) {
        closeButton.style.cursor = 'pointer';
        closeButton.onclick = function() {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        };
    }

    // Force reflow to ensure transition works
    toast.offsetHeight;
    toast.classList.add('show');
}

document.addEventListener('DOMContentLoaded', function() {
    // Handle clean up of modals
    document.addEventListener('hidden.bs.modal', function() {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });

    // Password toggle functions
    function togglePassword() {
        var passwordField = document.getElementById('password');
        passwordField.type = passwordField.type === 'password' ? 'text' : 'password';
    }

    function toggleRegisterPassword() {
        var passwordField = document.getElementById('registerPassword');
        var confirmPasswordField = document.getElementById('confirmPassword');
        var newType = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = newType;
        confirmPasswordField.type = newType;
    }

    // Handle form submissions
    // Login form
// Login form
const loginForm = document.querySelector('form[action*="login"]');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch(this.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(Object.fromEntries(new FormData(this)))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Successfully logged in!', 'success');
                // Close modal only on success
                const modal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
                if (modal) {
                    modal.hide();
                }
                // Redirect if needed
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } else {
                // Show error message but keep modal open
                showToast(data.error || 'Invalid credentials', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Login failed. Please try again.', 'error');
        });
    });
}


    // Forgot password form
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Your forgot password form submission logic
            showToast('Password reset link has been sent to your email. Check spam messages', 'success');
            // Submit the form
            this.submit();
        });
    }

    // Show initial flash messages as toasts
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');

    if (successMessage) {
        showToast(successMessage.textContent, 'success');
        successMessage.remove();
    }

    if (errorMessage) {
        showToast(errorMessage.textContent, 'error');
        errorMessage.remove();
    }

    // Scroll to top functionality
    const scrollTopBtn = document.getElementById("scroll-top");
    if (scrollTopBtn) {
        window.addEventListener("scroll", function() {
            scrollTopBtn.style.display = window.scrollY > 200 ? "flex" : "none";
        });

        scrollTopBtn.addEventListener("click", function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });
    }
});

// Function to clear error messages
function clearErrors() {
    document.querySelectorAll('.invalid-feedback').forEach(element => {
        element.textContent = '';
    });
    document.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });
}

// Function to display error messages
function displayErrors(errors) {
    Object.keys(errors).forEach(field => {
        const element = document.querySelector(`[name="${field}"]`);
        const errorDisplay = document.getElementById(`${field}Error`);
        if (element && errorDisplay) {
            element.classList.add('is-invalid');
            errorDisplay.textContent = errors[field][0];
        }
    });
}

// Registration form handling
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            clearErrors();

            // Create FormData object
            const formData = new FormData(this);

            // Show loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const spinner = submitButton.querySelector('.spinner-border');
            submitButton.disabled = true;
            if (spinner) spinner.classList.remove('d-none');

            // Send the request
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Registration successful! Please check your email for verification.', 'success');
                    
                    // Close the registration modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('registerModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // Redirect if needed
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        displayErrors(data.errors);
                        // Show first error in toast
                        const firstError = Object.values(data.errors)[0][0];
                        showToast(firstError, 'error');
                    } else {
                        showToast(data.message || 'Registration failed. Please try again.', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred during registration. Please try again.', 'error');
            })
            .finally(() => {
                // Reset button state
                submitButton.disabled = false;
                if (spinner) spinner.classList.add('d-none');
            });
        });
    }
});

// Password toggle functions
function toggleRegisterPassword() {
    const passwordField = document.getElementById('registerPassword');
    const button = passwordField.nextElementSibling.querySelector('i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        button.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passwordField.type = 'password';
        button.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

function toggleConfirmPassword() {
    const passwordField = document.getElementById('confirmPassword');
    const button = passwordField.nextElementSibling.querySelector('i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        button.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        passwordField.type = 'password';
        button.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>

</body>
</html>