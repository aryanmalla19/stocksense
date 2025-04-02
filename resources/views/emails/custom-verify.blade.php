<!DOCTYPE html>
<html>

<head>
    <style>
    body {
        font-family: Arial, sans-serif;
        text-align: center;
    }

    .container {
        padding: 20px;
    }

    .button {
        background-color: #3498db;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        display: inline-block;
        margin-top: 20px;
    }

    img {
        max-width: 200px;
        margin-bottom: 20px;
    }
    </style>
</head>

<body>
    <div class="container">
        <img src="{{ asset('images/logo.png') }}" alt="Stock-Sense Logo">
        <h2>Welcome, {{ $user->name }}!</h2>
        <p>Click the button below to verify your email address:</p>
        <p>Please take a moment to ensure we have the right email address for you!</p>
        <a href="{{ $url }}" class="button">Verify Email Address</a>
        <p>If you did not request this, ignore this email.</p>
    </div>
</body>

</html>