<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset | Stock Sense</title>
    <!-- Tailwind CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Inline styles for email client compatibility */
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
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Hello, {{ $user->name }}!</h2>
            <p class="text-gray-600 mb-6">Click the button below to reset your password:</p>
            <a href="{{ $url }}" class="button inline-block bg-blue-600 text-white font-medium py-3 px-6 rounded-md hover:bg-blue-700 transition-colors duration-200">Reset Your Password</a>
            <hr class="my-6 border-gray-200">
            <p class="text-sm text-gray-500 mb-2">Please take a moment to ensure we have the correct email address for you.</p>
            <p class="text-sm text-gray-500">If you didn’t request this, you can safely ignore this email.</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="w-full max-w-lg text-center py-4 text-gray-400 text-xs">
        <p>Stock Sense Inc.</p>
        <p>Providence, RI 02903, Nepal</p>
        <a href="#" class="underline hover:text-gray-600">Unsubscribe</a>
    </div>
</div>
</body>
</html>
