<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPO Allotment Confirmation | Stock Sense</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @media (max-width: 600px) {
            .container {
                padding: 16px;
            }
            .header-logo {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans leading-normal">
<div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
    <!-- Header -->
    <header class="mb-8 text-center">
        <img src="{{ asset('images/logo.png') }}" alt="Stock Sense Logo" class="header-logo w-16 h-16 mx-auto mb-4">
        <h1 class="text-3xl font-semibold text-gray-900">Stock Sense</h1>
    </header>

    <!-- Main Content -->
    <main class="w-full max-w-lg bg-white rounded-xl shadow-lg p-8">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-green-700 mb-2">Congratulations!</h2>
            <p class="text-lg text-gray-700 mb-4">Dear {{ $user->name }},</p>

            <p class="text-gray-600 mb-6">
                We are pleased to inform you that you have been successfully allotted shares in the IPO of:
            </p>

            <div class="bg-blue-100 rounded-lg p-4 mb-6">
                <p class="text-lg font-medium text-gray-900">
                    {{ $ipo->stock->company_name }} ({{ $ipo->stock->symbol }})
                </p>
                <div class="mt-3 text-sm text-gray-700 space-y-1">
                    <p>Allotted Shares: <span class="font-semibold">{{ $allottedShares }}</span></p>
                    <p>Issue Price per Share: <span class="font-semibold">Rs. {{ number_format($ipo->issue_price, 2) }}</span></p>
                    <p>Total Investment: <span class="font-semibold">Rs. {{ number_format($ipo->issue_price * $allottedShares, 2) }}</span></p>
                </div>
            </div>

            <p class="text-gray-600 text-sm mb-6">
                Thank you for choosing Stock Sense. Should you have any questions or require further assistance, please contact our support team at <a href="mailto:support@stocksense.com" class="text-blue-600 hover:underline">support@stocksense.com</a>.
            </p>

            <a href="" class="inline-block bg-green-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-green-700 transition">
                View Your Portfolio
            </a>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-10 text-center text-sm text-gray-500">
        <p class="mb-1">&copy; {{ date('Y') }} Stock Sense Inc. All rights reserved.</p>
        <p class="mb-2">Sanepa, Kathmandu, Nepal</p>
        <div class="space-x-4">
            <a href="" class="hover:text-gray-700">Contact Us</a>
            <span>|</span>
            <a href="" class="hover:text-gray-700">Unsubscribe</a>
        </div>
    </footer>
</div>
</body>
</html>
