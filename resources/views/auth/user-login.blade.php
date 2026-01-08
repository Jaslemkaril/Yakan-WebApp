@extends('layouts.app')

@section('title', 'Login - Yakan')

@push('styles')
<style>
    .auth-container {
        background: #800000;
        min-height: 100vh;
        position: relative;
        overflow: hidden;
    }

    .auth-container::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(251, 146, 60, 0.05) 0%, transparent 70%);
        animation: rotate 60s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .auth-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        overflow: hidden;
        position: relative;
    }

    .auth-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #dc2626, #ea580c);
    }

    .auth-form {
        padding: 2rem;
    }

    @media (min-width: 768px) {
        .auth-form {
            padding: 3rem;
        }
    }

    .social-login-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 12px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: white;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        width: 100%;
    }

    .social-login-btn:hover {
        border-color: #dc2626;
        background: #fef2f2;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.15);
    }

    .divider {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 2rem 0;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e5e7eb;
    }

    .divider span {
        color: #9ca3af;
        font-size: 14px;
        font-weight: 500;
    }

    .input-group {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .input-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        transition: color 0.3s ease;
        pointer-events: none;
    }

    .auth-input {
        width: 100%;
        padding: 14px 16px 14px 48px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: white;
    }

    .auth-input:focus {
        outline: none;
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }

    .auth-input:focus ~ .input-icon {
        color: #dc2626;
    }

    .remember-me {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 1.5rem;
    }

    .remember-me input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #dc2626;
    }

    .auth-illustration {
        background: linear-gradient(135deg, #dc2626 0%, #ea580c 100%);
        padding: 2rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        color: white;
        border-radius: 24px;
    }

    @media (min-width: 1024px) {
        .auth-illustration {
            padding: 3rem;
        }
    }

    .feature-list {
        list-style: none;
        padding: 0;
        margin: 2rem 0;
    }

    .feature-list li {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 1rem;
        font-size: 16px;
    }

    .feature-list li::before {
        content: '✓';
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        font-weight: bold;
        flex-shrink: 0;
    }

    .error-message {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #dc2626 0%, #ea580c 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .btn-primary:hover:not(:disabled) {
        background: linear-gradient(135deg, #b91c1c 0%, #c2410c 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);
    }

    .btn-primary:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .text-gradient {
        background: linear-gradient(135deg, #dc2626 0%, #ea580c 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
</style>
@endpush

@section('content')
<div class="auth-container relative">
    <!-- Hide main header for auth page -->
    <style>
        body > header {
            display: none;
        }
        body > footer {
            display: none;
        }
    </style>
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="max-w-6xl w-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <!-- Login Form -->
                <div class="auth-card animate-fade-in-up">
                    <div class="auth-form">
                        <!-- Logo -->
                        <div class="text-center mb-8">
                            <div class="flex items-center justify-center space-x-3 mb-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                                    <span class="text-white font-bold text-xl">Y</span>
                                </div>
                                <span class="text-2xl font-bold text-gradient">Yakan</span>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-900">Welcome Back</h2>
                            <p class="text-gray-600 mt-2">Sign in to your account to continue</p>
                        </div>

                        <!-- Social Login -->
                        <div class="space-y-3 mb-6">
                            <a href="{{ route('auth.redirect', 'google') }}" class="social-login-btn w-full">
                                <svg class="w-5 h-5" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                <span>Continue with Google</span>
                            </a>
                            
                            <a href="{{ route('auth.redirect', 'facebook') }}" class="social-login-btn w-full">
                                <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                                <span>Continue with Facebook</span>
                            </a>
                        </div>

                        <div class="divider">
                            <span>OR</span>
                        </div>

                        <!-- Login Form -->
                        <form method="POST" action="{{ route('login.user.submit') }}">
                            @csrf
                            
                            <div class="input-group">
                                <input 
                                    id="email" 
                                    type="email" 
                                    name="email" 
                                    class="auth-input" 
                                    placeholder="Email address"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email"
                                    autofocus
                                >
                                <label for="email" class="input-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                    </svg>
                                </label>
                            </div>

                            @error('email')
                                <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
                            @enderror

                            <div class="input-group">
                                <input 
                                    id="password" 
                                    type="password" 
                                    name="password" 
                                    class="auth-input" 
                                    placeholder="Password"
                                    required
                                    autocomplete="current-password"
                                >
                                <label for="password" class="input-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </label>
                            </div>

                            @error('password')
                                <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
                            @enderror

                            <div class="remember-me">
                                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label for="remember" class="text-sm text-gray-700">Remember me</label>
                            </div>

                            <button type="submit" class="btn-primary w-full text-lg py-3">
                                Sign In
                            </button>
                        </form>

                        <!-- Forgot Password -->
                        <div class="text-center mt-6">
                            <a href="{{ route('password.request') }}" class="text-red-600 hover:text-red-700 font-medium text-sm">
                                Forgot your password?
                            </a>
                        </div>

                        <!-- Sign Up Link -->
                        <div class="text-center mt-8">
                            <p class="text-gray-600">
                                Don't have an account? 
                                <a href="{{ route('register') }}" class="text-red-600 hover:text-red-700 font-medium">
                                    Sign up for free
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Illustration Side -->
                <div class="hidden lg:block">
                    <div class="auth-illustration rounded-2xl">
                        <div class="w-24 h-24 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-8">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 10-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-3xl font-bold mb-4">Welcome Back to Yakan</h3>
                        <p class="text-red-100 mb-8 text-lg">
                            Access your personalized shopping experience and track your orders
                        </p>

                        <ul class="feature-list">
                            <li>Track your orders in real-time</li>
                            <li>Save items to your wishlist</li>
                            <li>Exclusive member deals</li>
                            <li>Faster checkout process</li>
                            <li>Order history and receipts</li>
                        </ul>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
