<!DOCTYPE html>
<html>
<head>
    <title>Reset Your Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
        }
        .email-container {
            background: white;
            padding: 20px;
            border-radius: 5px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .button {
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin-top: 20px;
            font-size: 16px;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .footer {
            font-size: 12px;
            color: #888;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h2>🔒 Password Reset Request</h2>
        <p>We received a request to reset your password.</p>
        
        <a class="button" href="{{ url('password/reset/'.$token.'?email='.$email) }}">Reset Password</a>

        <p>This link will expire in 60 minutes.</p>
        <p>If you did not request this, ignore this email.</p>

        <p class="footer"><strong>- Lakbe Pampanga Team</strong></p>
    </div>
</body>
</html>
