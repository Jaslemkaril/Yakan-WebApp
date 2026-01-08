<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ucfirst($provider) }} Login - Sandbox Mode</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Static Assets (Vite build disabled for Expo frontend) -->
    
    <style>
        .sandbox-container {
            background: linear-gradient(135deg, #fef3c7 0%, #fed7aa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .sandbox-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }

        .sandbox-header {
            background: {{ $provider === 'google' ? 'linear-gradient(135deg, #4285F4 0%, #34A853 100%)' : 'linear-gradient(135deg, #1877F2 0%, #0866FF 100%)' }};
            padding: 2rem;
            text-align: center;
            color: white;
        }

        .sandbox-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .provider-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .sandbox-body {
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: {{ $provider === 'google' ? '#4285F4' : '#1877F2' }};
            box-shadow: 0 0 0 3px {{ $provider === 'google' ? 'rgba(66, 133, 244, 0.1)' : 'rgba(24, 119, 242, 0.1)' }};
        }

        .btn-sandbox {
            width: 100%;
            padding: 1rem;
            background: {{ $provider === 'google' ? 'linear-gradient(135deg, #4285F4 0%, #34A853 100%)' : 'linear-gradient(135deg, #1877F2 0%, #0866FF 100%)' }};
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-sandbox:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px {{ $provider === 'google' ? 'rgba(66, 133, 244, 0.3)' : 'rgba(24, 119, 242, 0.3)' }};
        }

        .info-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .info-box p {
            margin: 0;
            font-size: 0.875rem;
            color: #92400e;
        }

        .quick-fill {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .quick-fill-btn {
            padding: 0.25rem 0.75rem;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .quick-fill-btn:hover {
            background: #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="sandbox-container">
        <div class="sandbox-card">
            <div class="sandbox-header">
                <div class="sandbox-badge">
                    🧪 SANDBOX MODE
                </div>
                <div class="provider-logo">
                    @if($provider === 'google')
                        <svg class="w-12 h-12" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                    @else
                        <svg class="w-12 h-12" fill="#1877F2" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    @endif
                </div>
                <h1 class="text-2xl font-bold">{{ ucfirst($provider) }} Login Sandbox</h1>
                <p class="text-sm opacity-90 mt-2">Testing Mode - No Real OAuth Required</p>
            </div>

            <div class="sandbox-body">
                <div class="info-box">
                    <p><strong>ℹ️ Sandbox Mode Active</strong></p>
                    <p class="mt-1">This is a testing environment. Enter any name and email to simulate {{ ucfirst($provider) }} login.</p>
                </div>

                @if(session('error'))
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('auth.social.sandbox.login', $provider) }}">
                    @csrf

                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-input" 
                            placeholder="John Doe"
                            value="{{ old('name') }}"
                            required
                        >
                        <div class="quick-fill">
                            <button type="button" class="quick-fill-btn" onclick="quickFill('John Doe', 'john@example.com')">
                                Quick Fill 1
                            </button>
                            <button type="button" class="quick-fill-btn" onclick="quickFill('Jane Smith', 'jane@example.com')">
                                Quick Fill 2
                            </button>
                            <button type="button" class="quick-fill-btn" onclick="quickFill('Test User', 'test@example.com')">
                                Quick Fill 3
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="john@example.com"
                            value="{{ old('email') }}"
                            required
                        >
                    </div>

                    <button type="submit" class="btn-sandbox">
                        Continue with {{ ucfirst($provider) }}
                    </button>
                </form>

                <div class="text-center mt-6">
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                        ← Back to Login
                    </a>
                </div>

                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-xs text-gray-600 text-center">
                        <strong>Note:</strong> This sandbox simulates {{ ucfirst($provider) }} OAuth without requiring API credentials. Perfect for development and testing!
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function quickFill(name, email) {
            document.getElementById('name').value = name;
            document.getElementById('email').value = email;
        }
    </script>
</body>
</html>
