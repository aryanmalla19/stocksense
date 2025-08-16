<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - Stock Sense</title>
    <!-- Tailwind CSS via CDN for simplicity; in production, consider compiling it -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Additional inline styles for email client compatibility */
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .button { width: 100% !important; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
<div class="flex flex-col items-center justify-center min-h-screen">
    <!-- Header -->
    <div class="w-full max-w-lg text-center py-6">
        <h1 class="text-3xl font-bold text-gray-800">Stock Sense</h1>
    </div>

    <!-- Main Content -->
    <div class="container w-full max-w-lg bg-white shadow-lg rounded-lg p-8">
        <div class="text-center">
            <img src="{{ asset('images/logo.png') }}" alt="Stock Sense Logo" class="mx-auto mb-6 max-w-[150px]">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Welcome, {{ $user->name }}!</h2>
            <p class="text-gray-600 mb-6">We’re excited to have you on board. Please verify your email address to get started.</p>
            <a href="{{ $url }}" class="button inline-block bg-blue-600 text-white font-medium py-3 px-6 rounded-md hover:bg-blue-700 transition-colors duration-200">Verify Email Address</a>
            <hr class="my-6 border-gray-200">
            <p class="text-sm text-gray-500 mb-2">Please ensure this is the correct email address for your account.</p>
            <p class="text-sm text-gray-500">If you didn’t request this, feel free to ignore this email.</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="w-full max-w-lg text-center py-4 text-gray-400 text-xs">
        <p>Stock Sense, Inc.</p>
        <p>Providence, RI 02903, Nepal</p>
        <a href="#" class="underline hover:text-gray-600">Unsubscribe</a>
    </div>
</div>
</body>
</html>
