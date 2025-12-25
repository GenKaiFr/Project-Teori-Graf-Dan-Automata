<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Meeting Scheduler</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
        <div class="text-center mb-6">
            <i class="fas fa-calendar-alt text-4xl text-blue-600 mb-2"></i>
            <h1 class="text-2xl font-bold text-gray-800">Meeting Scheduler</h1>
            <p class="text-gray-600">Login to your account</p>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                           class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="button" onclick="togglePassword('password')" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i id="password-icon" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600">Don't have an account? 
                <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800">Register here</a>
            </p>
        </div>

        <div class="mt-4 text-center text-sm text-gray-500">
            <p><strong>Demo Accounts:</strong></p>
            <p>Admin: admin@demo.com / password</p>
            <p>Manager: manager@demo.com / password</p>
            <p>User: user@demo.com / password</p>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '-icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>