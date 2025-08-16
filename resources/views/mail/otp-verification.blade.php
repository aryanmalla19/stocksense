<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your OTP | Stock Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 font-sans text-center">
<div class="min-h-screen flex flex-col items-center justify-center px-4 py-8">
    <!-- Header -->
    <div class="mb-4">
        <h1 class="text-3xl font-bold text-gray-800">Stock Sense</h1>
    </div>

    <!-- Main Container -->
    <div class="w-full max-w-md bg-white rounded-2xl shadow-md p-6">
        <div class="flex flex-col items-center">
            <img src="{{ asset('images/logo.png') }}" alt="Stock-Sense Logo" class="w-32 h-32 mb-4">

            <h2 class="text-xl font-semibold text-gray-700">Hello, {{ $user->name }} 👋</h2>
            <p class="text-gray-600 mt-2">Here is your One-Time Password (OTP):</p>

            <h1 class="text-4xl font-bold text-blue-600 my-4 tracking-widest">{{ $otp }}</h1>

            <hr class="w-full border-gray-200 my-4">

            <p class="text-sm text-gray-600">
                Please take a moment to ensure we have the correct email address for you.
            </p>
            <p class="text-sm text-gray-600 mt-1">
                If you did not request this OTP, you can safely ignore this email.
            </p>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-xs text-gray-400 mt-6">
        <p>Stock Sense Inc.</p>
        <p>Sanepa, Kathmandu, Nepal</p>
        <a href="#" class="underline hover:text-gray-500">Unsubscribe</a>
    </div>
</div>
</body>

</html>
