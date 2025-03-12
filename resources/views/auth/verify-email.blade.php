<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - Lakbe Pampanga</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .verify-email-container {
            width: 100%;
            max-width: 600px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }
        .card-body {
            padding: 2rem;
        }
        .logo {
            max-height: 80px;
            margin-bottom: 1rem;
        }
        .alert {
            border-radius: 10px;
        }
        .verification-buttons {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        .btn-outline-secondary {
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        .icon-envelope {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
        .footer-note {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 2rem;
            padding: 0 1rem;
        }
    </style>
</head>
<body>
    <div class="container verify-email-container">
        <div class="text-center mb-4">
            <img src="{{ asset('img/lakbe-logo1.png') }}" alt="Lakbe Pampanga Logo" class="logo">
        </div>

        <div class="card">
            <div class="card-header text-center">
                <h4 class="mb-0">Verify Your Email Address</h4>
            </div>

            <div class="card-body">
                @if (session('resent'))
                    <div class="alert alert-success text-center" role="alert">
                        A fresh verification link has been sent to your email address.
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger text-center" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="text-center">
                    <i class="bi bi-envelope-check icon-envelope"></i>
                    <p class="mb-4">
                        Before proceeding, please check your email for a verification link.<br>
                        If you didn't receive the email, we can send you another one.
                    </p>
                </div>

                <div class="verification-buttons">
                    <form method="POST" action="{{ route('verification.send') }}" class="w-100 text-center">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-2"></i>Resend Verification Email
                        </button>
                    </form>

                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-house me-2"></i>Return to Home
                    </a>
                </div>

                <div class="text-center footer-note">
                    <small>
                        If you're having trouble clicking the verification button, copy and paste the URL from the email into your web browser.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>