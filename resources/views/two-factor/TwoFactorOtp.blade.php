<!DOCTYPE html>
<html>

<head>
    <style>
    body {
        font-family: Arial, sans-serif;
        text-align: center;
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

    .container {
        width: 40%;
        border: 20px;
        background-color: white;
        border-radius: 10px;
    }

    body {
        background-color: #eeeeee;
        display: flex;
        /* align-items: center; */
        justify-content: center;
    }

    .company_name {
        size: 50px;
        font-weight: 500;
    }
    </style>
</head>

<body>
    <div class="container">
        <img src="{{ asset('images/logo.png') }}" alt="Stock-Sense Logo">
        <h2>Hello, {{ $user->name }}!</h2>
        <p>Find out your OTP below:</p>
        <p>Please take a moment to ensure we have the right email address for you!</p>
        <p>Your OTP is:</p>
        <h3>{{ $otp }}</h3>
        <p>If you did not request this, ignore this email.</p>
    </div>
</body>


</html>