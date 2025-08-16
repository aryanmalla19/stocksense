<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login with Social Accounts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-100 font-[Inter] flex items-center justify-center min-h-screen">
    <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-md">
        <h2 class="text-2xl font-semibold text-center text-gray-800 mb-6">Sign in to Your Account</h2>

        <div class="space-y-4">
            <!-- Google Login -->
            <a href="{{ route('auth.google')}}"
                class="flex items-center justify-center py-3 px-4 rounded-xl bg-white border border-gray-300 hover:shadow-md transition w-full">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="w-5 h-5 mr-3">
                <span class="text-gray-700 font-medium">Continue with Google</span>
            </a>

            <!-- Facebook Login -->
            <a
                class="flex items-center justify-center py-3 px-4 rounded-xl bg-[#1877F2] hover:bg-[#166fe0] text-white transition w-full">
                <img src="https://www.svgrepo.com/show/157806/facebook.svg" alt="Facebook"
                    class="w-5 h-5 mr-3 bg-white rounded-full p-0.5">
                <span class="font-medium">Continue with Facebook</span>
            </a>
        </div>

        <div class="mt-6 text-center text-sm text-gray-500">
            Don't have an account? <a href="#" class="text-blue-500 hover:underline">Sign up</a>
        </div>
    </div>
</body>

</html>