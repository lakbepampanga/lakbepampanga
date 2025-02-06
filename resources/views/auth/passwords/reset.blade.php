<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
  <script src="{{ asset('js/main.js') }}"></script>
    <style>
        .show-password-toggle {
            cursor: pointer;
            user-select: none;
        }

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
<body>
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
    <li><a href="/" class="{{ request()->is('index') ? 'active' : '' }}">Home</a></li>
</ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

    </div>
</header>
<main class="main container d-flex justify-content-center align-items-center mt-5 vh-100">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg border-0 rounded-3 p-4">
            <h2 class="text-center mb-4">Reset Password</h2>

            <!-- Flash Success Message -->
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group mb-3">
                    <label for="password">New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" required>
                        <span class="input-group-text show-password-toggle" onclick="togglePassword('password')">Show</span>
                    </div>
                </div>

                <div class="form-group mb-3">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm new password" required>
                        <span class="input-group-text show-password-toggle" onclick="togglePassword('password_confirmation')">Show</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-custom w-100">Reset Password</button>
            </form>
        </div>
    </div>
</main>


<footer id="footer" class="footer dark-background w-100">
  <div class="container-fluid text-center py-4">
    <p>© <span>Copyright</span> <strong class="px-1 sitename">Lakbe Pampanga</strong> <span>All Rights Reserved</span></p>
    <div class="credits">
      Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a> Distributed By <a href="https://themewagon.com">ThemeWagon</a>
    </div>
  </div>
</footer>


    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleText = passwordField.nextElementSibling;

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleText.textContent = 'Hide';
            } else {
                passwordField.type = 'password';
                toggleText.textContent = 'Show';
            }
        }
    </script>
</body>
</html>
