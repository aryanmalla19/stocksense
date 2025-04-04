<!DOCTYPE html>
<html>

<head>
    <style>
    body {
        font-family: Arial, sans-serif;
        text-align: center;
        background-color: #eeeeee;
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

    .company_name {
        size: 50px;
        font-weight: 500;
    }

    .container {
        width: 40%;
        border: 20px;
        background-color: white;
        border-radius: 10px;
        padding-top: 20px;
        padding-bottom: 20px
    }

    .container1 {
        background-color: #eeeeee;
        color: rgba(0, 0, 0, 0.29);
        font-size: 10px;
    }

    .container2 {
        background-color: #eeeeee;
        color: black;
        font-size: 22px;
    }

    .main-container {
        margin-top: 30px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="container2">
            <h1>Stock Sense</h1>
        </div>
        <div class="container">
            <img src="{{ asset('images/logo.png') }}" alt="Stock-Sense Logo">
            <h2>Hello, {{ $user->name }}!</h2>
            <p>Click the button below to reset your password:</p>
            <a href="{{ $url }}" class="button">Reset Your Password</a>
            <p>Please take a moment to ensure we have the right email address for you!</p>
            <p>If you did not request this, ignore this email.</p>
        </div>
        <div class="container1">
            <p>Stock Sense.Inc.</p>
            <p>Providence, RI 02903 Nepal</p>
            <a style="text-decoration:underline">unsubscribe</a>
        </div>
    </div>
</body>

</html>
