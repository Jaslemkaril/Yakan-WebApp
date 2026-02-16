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
        backdrop-filter: blur(10px);
    }

    .auth-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #dc2626, #ea580c, #dc2626);
        background-size: 200% 100%;
        animation: shimmer 3s linear infinite;
    }

    @keyframes shimmer {
        0% { background-position: -200% 0; }
        100% { background-position: 200% 0; }
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
        padding: 14px 24px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: white;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        width: 100%;
        position: relative;
        overflow: hidden;
    }

    .social-login-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(220, 38, 38, 0.05), transparent);
        transition: left 0.5s ease;
    }

    .social-login-btn:hover::before {
        left: 100%;
    }

    .social-login-btn:hover {
        border-color: #4285F4;
        background: #f8faff;
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(66, 133, 244, 0.2);
    }

    .social-login-btn:active {
        transform: translateY(0);
    }

    .divider {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        margin: 2rem 0;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        height: 2px;
        background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
    }

    .divider span {
        color: #6b7280;
        font-size: 14px;
        font-weight: 600;
        letter-spacing: 0.5px;
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
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
    }

    .auth-input::placeholder {
        color: #9ca3af;
    }

    .auth-input:hover {
        border-color: #d1d5db;
    }

    .auth-input:focus {
        outline: none;
        border-color: #dc2626;
        box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
        background: #ffffff;
    }

    .auth-input:focus ~ .input-icon {
        color: #dc2626;
    }

    .remember-me {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 1.5rem;
        cursor: pointer;
    }

    .remember-me input[type="checkbox"] {
        width: 20px;
        height: 20px;
        accent-color: #dc2626;
        cursor: pointer;
        border-radius: 4px;
    }

    .remember-me label {
        cursor: pointer;
        user-select: none;
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
        text-align: left;
    }

    .feature-list li {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 1.25rem;
        font-size: 17px;
        padding: 12px 16px;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .feature-list li:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateX(8px);
    }

    .error-message {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border: 1px solid #fecaca;
        border-left: 4px solid #dc2626;
        color: #dc2626;
        padding: 12px 16px;
        border-radius: 10px;
        font-size: 14px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 2px 8px rgba(220, 38, 38, 0.1);
        animation: shake 0.4s ease;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }

    .btn-primary {
        background: linear-gradient(135deg, #dc2626 0%, #ea580c 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, transparent 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .btn-primary:hover:not(:disabled)::before {
        opacity: 1;
    }

    .btn-primary:hover:not(:disabled) {
        background: linear-gradient(135deg, #b91c1c 0%, #c2410c 100%);
        transform: translateY(-2px);
        box-shadow: 0 12px 35px rgba(220, 38, 38, 0.4);
    }

    .btn-primary:active:not(:disabled) {
        transform: translateY(0);
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

    @keyframes fade-in-up {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fade-in-up 0.6s ease-out;
    }

    .link-hover {
        transition: all 0.2s ease;
    }

    .link-hover:hover {
        text-decoration: underline;
        transform: translateX(2px);
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
                                <div class="w-14 h-14 bg-gradient-to-br from-red-600 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-105 transition-transform duration-300">
                                    <span class="text-white font-bold text-2xl">Y</span>
                                </div>
                                <span class="text-3xl font-bold text-gradient">Yakan</span>
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back</h2>
                            <p class="text-gray-600 text-base">Sign in to your account to continue</p>
                        </div>

                        <!-- Social Login -->
                        <div class="mb-6">
                            <a href="{{ route('auth.redirect', 'google') }}" class="social-login-btn w-full">
                                <svg class="w-5 h-5" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                <span>Continue with Google</span>
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

                            <button type="submit" class="btn-primary w-full text-lg py-3.5 font-semibold">
                                <span class="relative z-10">Sign In</span>
                            </button>
                        </form>

                        <!-- Forgot Password -->
                        <div class="text-center mt-6">
                            <a href="{{ route('password.request') }}" class="text-red-600 hover:text-red-700 font-medium text-sm link-hover inline-block">
                                Forgot your password?
                            </a>
                        </div>

                        <!-- Sign Up Link -->
                        <div class="text-center mt-8 pt-6 border-t border-gray-100">
                            <p class="text-gray-600">
                                Don't have an account? 
                                <a href="{{ route('register') }}" class="text-red-600 hover:text-red-700 font-semibold link-hover inline-block">
                                    Sign up for free
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Illustration Side -->
                <div class="hidden lg:block">
                    <div class="auth-illustration rounded-2xl">
                        <div class="w-28 h-28 bg-white/20 backdrop-blur-sm rounded-3xl flex items-center justify-center mb-8 shadow-2xl transform hover:scale-105 transition-transform duration-300">
                            <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 10-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-4xl font-bold mb-4">Welcome Back to Yakan</h3>
                        <p class="text-red-100 mb-8 text-lg leading-relaxed">
                            Access your personalized shopping experience and track your orders
                        </p>

                        <ul class="feature-list">
                            <li>📦 Track your orders in real-time</li>
                            <li>❤️ Save items to your wishlist</li>
                            <li>🎁 Exclusive member deals</li>
                            <li>⚡ Faster checkout process</li>
                            <li>📋 Order history and receipts</li>
                        </ul>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
