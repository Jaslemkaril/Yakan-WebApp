<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Yakan E-commerce</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
        
        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        .animate-slide-left {
            animation: slideInLeft 0.6s ease-out forwards;
        }
        
        .animate-slide-right {
            animation: slideInRight 0.6s ease-out forwards;
        }
        
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        .animate-rotate-slow {
            animation: rotate 20s linear infinite;
        }
        
        .pattern-grid {
            background-image: linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 30px 30px;
        }
        
        .input-focus:focus {
            border-color: #800000 !important;
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1) !important;
        }
        
        .show-password {
            cursor: pointer;
            user-select: none;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="background: linear-gradient(to bottom right, #1a0000, #330000, #1a0000);">
    <div class="w-full max-w-6xl grid lg:grid-cols-2 gap-8 items-center">
        <!-- Left Side - Admin Login Form -->
        <div class="w-full max-w-md mx-auto lg:order-1 animate-slide-left">
            <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-12 border border-gray-100">
                <!-- Logo -->
                <div class="flex items-center justify-center space-x-3 mb-8">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" style="background: linear-gradient(to bottom right, #800000, #600000);">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold" style="color: #800000;">Yakan Admin</span>
                </div>

                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Admin Access</h2>
                    <p class="text-gray-600">Sign in to manage your platform</p>
                </div>

                <!-- Display validation errors -->
                <div id="error-container" style="display: none;" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-red-800 mb-1">Authentication Failed</h3>
                            <ul id="error-list" class="list-disc list-inside text-sm text-red-700 space-y-1">
                                <!-- Errors will be inserted here -->
                            </ul>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                    @csrf
                    
                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Admin Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                autofocus
                                autocomplete="username"
                                class="input-focus w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none transition-all duration-200"
                                placeholder="admin@yakan.com"
                                value="{{ old('email') }}"
                            >
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                autocomplete="current-password"
                                class="input-focus w-full pl-12 pr-12 py-3 border border-gray-300 rounded-xl focus:outline-none transition-all duration-200"
                                placeholder="••••••••"
                            >
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                <button type="button" onclick="togglePassword()" class="show-password text-gray-400 hover:text-gray-600 transition">
                                    <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="w-4 h-4 border-gray-300 rounded" style="accent-color: #800000;">
                        <label for="remember" class="ml-2 text-sm text-gray-700">Keep me signed in</label>
                    </div>

                    <!-- Security Notice -->
                    <div class="border rounded-xl p-4" style="background-color: #faf5f5; border-color: #e0b0b0;">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #800000;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm" style="color: #600000;">
                                    <span class="font-semibold">Security Notice:</span> This area is restricted to authorized administrators only.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full text-white py-3 rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                        style="background: linear-gradient(to right, #800000, #600000);"
                        onmouseover="this.style.background='linear-gradient(to right, #600000, #400000)'"
                        onmouseout="this.style.background='linear-gradient(to right, #800000, #600000)'"
                    >
                        Access Dashboard
                    </button>
                </form>

                <!-- Forgot Password Link -->
                <div class="mt-6 text-center">
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold hover:opacity-80 transition" style="color: #800000;">
                        Forgot your password?
                    </a>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="text-center mt-6">
                <a href="{{ route('welcome') }}" class="inline-flex items-center text-gray-400 hover:text-gray-200 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Home
                </a>
            </div>
        </div>

        <!-- Right Side - Admin Features Section -->
        <div class="hidden lg:block lg:order-2 animate-slide-right">
            <div class="relative">
                <!-- Decorative Background -->
                <div class="absolute inset-0 rounded-3xl transform -rotate-3 opacity-20" style="background: linear-gradient(to bottom right, #800000, #400000);"></div>
                
                <div class="relative rounded-3xl p-12 text-white pattern-grid shadow-2xl overflow-hidden" style="background: linear-gradient(to bottom right, #800000, #400000);">
                    <!-- Animated Background Elements -->
                    <div class="absolute top-10 right-10 w-40 h-40 bg-white/5 rounded-full animate-pulse-slow"></div>
                    <div class="absolute bottom-10 left-10 w-32 h-32 bg-white/5 rounded-full animate-pulse-slow" style="animation-delay: 1s;"></div>
                    
                    <div class="relative z-10">
                        <div class="mb-8">
                            <div class="inline-block px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-sm font-semibold mb-4">
                                Admin Dashboard
                            </div>
                            <h2 class="text-4xl font-bold mb-4">Command Center</h2>
                            <p class="text-indigo-100 text-lg">Manage your e-commerce platform with powerful tools</p>
                        </div>
                        
                        <!-- Central Admin Icon -->
                        <div class="flex justify-center mb-10">
                            <div class="relative">
                                <div class="w-32 h-32 bg-white/20 backdrop-blur-sm rounded-3xl flex items-center justify-center shadow-2xl">
                                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <div class="absolute -top-2 -right-2 w-8 h-8 bg-green-400 rounded-full flex items-center justify-center animate-pulse-slow">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-start space-x-4 bg-white/10 backdrop-blur-sm rounded-2xl p-5">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg mb-1">User Management</h3>
                                    <p class="text-indigo-100 text-sm">Monitor and manage all user accounts and permissions</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4 bg-white/10 backdrop-blur-sm rounded-2xl p-5">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg mb-1">Product Control</h3>
                                    <p class="text-indigo-100 text-sm">Add, edit, and organize your product catalog</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4 bg-white/10 backdrop-blur-sm rounded-2xl p-5">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg mb-1">Order Management</h3>
                                    <p class="text-indigo-100 text-sm">Track, process, and fulfill customer orders</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4 bg-white/10 backdrop-blur-sm rounded-2xl p-5">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg mb-1">Analytics & Reports</h3>
                                    <p class="text-indigo-100 text-sm">View insights, sales data, and performance metrics</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4 bg-white/10 backdrop-blur-sm rounded-2xl p-5">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-lg mb-1">System Settings</h3>
                                    <p class="text-indigo-100 text-sm">Configure platform settings and preferences</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-10 pt-8 border-t border-white/20">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <div class="text-2xl font-bold text-white mb-1">99.9%</div>
                                    <div class="text-indigo-100 text-xs">Uptime</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-white mb-1">24/7</div>
                                    <div class="text-indigo-100 text-xs">Monitoring</div>
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-white mb-1">Secure</div>
                                    <div class="text-indigo-100 text-xs">Encrypted</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }

        // Laravel error handling would populate this
        // const errors = @json($errors->all());
        // if (errors.length > 0) {
        //     document.getElementById('error-container').style.display = 'block';
        //     const errorList = document.getElementById('error-list');
        //     errors.forEach(error => {
        //         const li = document.createElement('li');
        //         li.textContent = error;
        //         errorList.appendChild(li);
        //     });
        // }
    </script>
</body>
</html>