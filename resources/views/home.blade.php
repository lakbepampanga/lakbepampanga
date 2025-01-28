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

  <!-- Vendor CSS Files -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

<!-- Main CSS File -->
<link href="{{ asset('css/main.css') }}" rel="stylesheet">


  <!-- Bootstrap CSS and JS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="index-page">

<header id="header" class="header bg-white d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

        <a href="home-.blade.php" class="logo d-flex align-items-center">
            <h1 class="sitename">Lakbe Pampanga</h1>
        </a>

        <nav id="navmenu" class="navmenu">
            <ul>
                <li><a href="#hero" class="active">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#team">Team</a></li>
                <li><a href="#" class="btn btn-primary btn-md px-3 py-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login</a></li>
                <li><a href="#" class="btn btn-secondary btn-md px-3 py-2" data-bs-toggle="modal" data-bs-target="#registerModal">Register</a></li>
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


<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginModalLabel">Login Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <div class="mb-3">
                        <input type="checkbox" id="showPassword" onclick="togglePassword()"> Show Password
                    </div>
                    <a href="#forgotPasswordModal" data-bs-toggle="modal" data-bs-dismiss="modal">Forgot Password?</a>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">Register</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">  
              <form id ="registerForm" method="POST" action="{{ route('register') }}">
                  @csrf
                  <div class="mb-3">
                      <label for="registerEmail" class="form-label">Email:</label>
                      <input type="email" id="registerEmail" name="email" class="form-control" placeholder="Enter your email" required>
                      @error('email')
                          <small class="text-danger">{{ $message }}</small>
                      @enderror
                  </div>
                  <div class="mb-3">
                      <label for="registerUsername" class="form-label">Username:</label>
                      <input type="text" id="registerUsername" name="name" class="form-control" placeholder="Enter your username" required>
                      @error('name')
                          <small class="text-danger">{{ $message }}</small>
                      @enderror
                  </div>
                  <div class="mb-3">
                      <label for="registerPassword" class="form-label">Password:</label>
                      <input type="password" id="registerPassword" name="password" class="form-control" placeholder="Enter your password" required>
                      @error('password')
                          <small class="text-danger">{{ $message }}</small>
                      @enderror
                  </div>
                  <div class="mb-3">
                      <label for="confirmPassword" class="form-label">Confirm Password:</label>
                      <input type="password" id="confirmPassword" name="password_confirmation" class="form-control" placeholder="Confirm your password" required>
                  </div>
                  <div class="mb-3">
                      <input type="checkbox" id="showPasswordReg" onclick="toggleRegisterPassword()"> Show Password
                  </div>
                  <button type="submit" class="btn btn-primary w-100">Register</button>
              </form>
              <p class="mt-3 text-center">
                  Already have an account? <a href="#loginModal" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal">Login here</a>
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

<main class="main">

 <!-- Hero Section -->
 <section id="hero" class="hero section light-background">
 <img src="{{ asset('img/hero-bg-2.jpg') }}" alt="" class="hero-bg">
  <div class="container d-flex align-items-center justify-content-center vh-100">
    <div class="text-center" data-aos="fade-in">
      <h1 class="hero-title">Lakbe<br>Pampanga</h1>
      <p class="mb-5">Your Ultimate Guide to Seamless Travel and Adventures in Pampanga</p>
      <div class="d-flex justify-content-center">
        <a href="#about" class="btn-get-started me-3" data-bs-toggle="modal" data-bs-target="#loginModal">Start Your Adventure</a>
      </div>
    </div>
  </div>
  
  <svg class="hero-waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28 " preserveAspectRatio="none">
    <defs>
      <path id="wave-path" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"></path>
    </defs>
    <g class="wave1">
      <use xlink:href="#wave-path" x="50" y="3"></use>
    </g>
    <g class="wave2">
      <use xlink:href="#wave-path" x="50" y="0"></use>
    </g>
    <g class="wave3">
      <use xlink:href="#wave-path" x="50" y="9"></use>
    </g>
  </svg>
</section>

  


  <!-- About to Section -->
  <section id="about" class="details section">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
      <h2>About Us</h2>
      <div><span>Lakbe</span> <span class="description-title">Pampanga</span></div>
    </div><!-- End Section Title -->

    <div class="container">

      <div class="row gy-4 align-items-center features-item">
        <div class="col-md-5 d-flex align-items-center" data-aos="zoom-out" data-aos-delay="100">
          <img src="{{ asset('img/about.webp') }}" class="img-fluid" alt="">

        </div>
        <div class="col-md-7" data-aos="fade-up" data-aos-delay="100">
          <h3>About Lakbe Pampanga</h3>
          <p class="fst-italic">
          Lakbe Pampanga aims to revolutionize how people explore the province by integrating a customizable itinerary planning, and to transform how residents and visitors wander the places in the province by providing options for various modes of public transportation. Lakbe Pampanga also generates an itinerary for the user whether there are activities that they want to accomplish based on the time that they set, but the user will also have the option to set a customized itinerary for their own.
          </p>
          <ul>
          <li><i class="bi bi-check"></i><span> Customizable itineraries tailored to your preferences and time availability.</span></li>
          <li><i class="bi bi-check"></i><span> Comprehensive travel options, including various modes of public transportation.</span></li>
          <li><i class="bi bi-check"></i><span> Smart itinerary suggestions based on activities and destinations in Pampanga.</span></li>
          <li><i class="bi bi-check"></i><span> User-friendly platform designed to enhance your travel experience.</span></li>
          <li><i class="bi bi-check"></i><span> Seamless integration of local insights to explore hidden gems in Pampanga.</span></li>

          </ul>
        </div>
      </div><!-- Features Item -->
    </div>

  </section><!-- About Section -->

<!-- Features Section -->
<section id="features" class="features section">
  <div class="container">
    <div class="row justify-content-center gy-4">
      <!-- Feature 1 -->
      <div class="col-lg-4 col-md-4 feature-item">
        <div class="features-item text-center">
          <i class="bi bi-watch" style="color: #ffbb2c;"></i>
          <h3>Less Time-Consuming</h3>
          <p class="justified-text">Lakbe Pampanga simplifies trip planning with automated, customizable itineraries, saving you valuable time.</p>
        </div>
      </div>
      <!-- End Feature 1 -->

      <!-- Feature 2 -->
      <div class="col-lg-4 col-md-4 feature-item">
        <div class="features-item text-center">
          <i class="bi bi-lightbulb" style="color: #5578ff;"></i>
          <h3>Convenient</h3>
          <p class="justified-text">Get easy access to detailed routes and transportation options across District 1 of Pampanga—navigate effortlessly!</p>
        </div>
      </div>
      <!-- End Feature 2 -->

      <!-- Feature 3 -->
      <div class="col-lg-4 col-md-4 feature-item">
        <div class="features-item text-center">
          <i class="bi bi-bar-chart-steps" style="color: #e80368;"></i>
          <h3>Personalized Travel</h3>
          <p class="justified-text">Whether you're on a tight schedule or exploring at leisure, Lakbe Pampanga tailors your itinerary for a smooth, enjoyable trip.</p>
        </div>
      </div>
      <!-- End Feature 3 -->
    </div>
  </div>
</section>





  <!-- Team Section -->
  <section id="team" class="team section">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
      <h2>Team</h2>
      <div><span>Lakbe Pampanga</span> <span class="description-title">Developers</span></div>
    </div><!-- End Section Title -->

    <div class="container">
  <div class="row gy-5">
    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
      <div class="member">
      <div class="pic"><img src="{{ asset('img/team/team-1.jpg') }}" class="img-fluid" alt=""></div>
        <div class="member-info">
          <h4>Michael Pamintuan</h4>
          <span>Chief Executive Officer</span>
          <div class="social">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>
      </div>
    </div><!-- End Team Member -->

    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
      <div class="member">
      <div class="pic"><img src="{{ asset('img/team/team-2.jpg') }}" class="img-fluid" alt=""></div>

        <div class="member-info">
          <h4>River Yuan</h4>
          <span>Product Manager</span>
          <div class="social">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>
      </div>
    </div><!-- End Team Member -->

    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
      <div class="member">
      <div class="pic"><img src="{{ asset('img/team/team-3.jpg') }}" class="img-fluid" alt=""></div>
        <div class="member-info">
          <h4>Matthew Ortiz</h4>
          <span>CTO</span>
          <div class="social">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>
      </div>
    </div><!-- End Team Member -->

    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
      <div class="member">
      <div class="pic"><img src="{{ asset('img/team/team-3.jpg') }}" class="img-fluid" alt=""></div>
        <div class="member-info">
          <h4>Ulrich Oconer</h4>
          <span>Marketing Specialist</span>
          <div class="social">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>
      </div>
    </div><!-- End Team Member -->
  </div>
</div>


  </section><!-- /Team Section -->

  <!-- Contact Section -->
  <section id="contact" class="contact section">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
      <h2>Contact</h2>
      <div><span>Check Our</span> <span class="description-title">Contact</span></div>
    </div><!-- End Section Title -->

    <div class="container" data-aos="fade" data-aos-delay="100">

      <div class="row gy-4">

        <div class="col-lg-4">
          <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="200">
            <i class="bi bi-geo-alt flex-shrink-0"></i>
            <div>
              <h3>Address</h3>
              <p>A108 Adam Street, New York, NY 535022</p>
            </div>
          </div><!-- End Info Item -->

          <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
            <i class="bi bi-telephone flex-shrink-0"></i>
            <div>
              <h3>Call Us</h3>
              <p>+1 5589 55488 55</p>
            </div>
          </div><!-- End Info Item -->

          <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
            <i class="bi bi-envelope flex-shrink-0"></i>
            <div>
              <h3>Email Us</h3>
              <p>info@example.com</p>
            </div>
          </div><!-- End Info Item -->

        </div>

        <div class="col-lg-8">
          <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
            <div class="row gy-4">

              <div class="col-md-6">
                <input type="text" name="name" class="form-control" placeholder="Your Name" required="">
              </div>

              <div class="col-md-6 ">
                <input type="email" class="form-control" name="email" placeholder="Your Email" required="">
              </div>

              <div class="col-md-12">
                <input type="text" class="form-control" name="subject" placeholder="Subject" required="">
              </div>

              <div class="col-md-12">
                <textarea class="form-control" name="message" rows="6" placeholder="Message" required=""></textarea>
              </div>

              <div class="col-md-12 text-center">
                <div class="loading">Loading</div>
                <div class="error-message"></div>
                <div class="sent-message">Your message has been sent. Thank you!</div>

                <button type="submit">Send Message</button>
              </div>

            </div>
          </form>
        </div><!-- End Contact Form -->

      </div>

    </div>

  </section><!-- /Contact Section -->

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

<!-- Preloader -->
<div id="preloader"></div>

<!-- Vendor JS Files -->
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/php-email-form/validate.js') }}"></script>
<script src="{{ asset('vendor/aos/aos.js') }}"></script>
<script src="{{ asset('vendor/glightbox/js/glightbox.min.js') }}"></script>
<script src="{{ asset('vendor/purecounter/purecounter_vanilla.js') }}"></script>
<script src="{{ asset('vendor/swiper/swiper-bundle.min.js') }}"></script>

<!-- Main JS File -->
<script src="{{ asset('js/main.js') }}"></script>

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

<!-- registration success message -->


</body>
</html>
