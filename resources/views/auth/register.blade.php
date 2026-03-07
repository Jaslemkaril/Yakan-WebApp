@extends('layouts.app')

@section('title', 'Register - Yakan')

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

    .password-strength {
        margin-top: 0.5rem;
        height: 4px;
        background: #e5e7eb;
        border-radius: 2px;
        overflow: hidden;
    }

    .password-strength-bar {
        height: 100%;
        transition: all 0.3s ease;
        border-radius: 2px;
    }

    .strength-weak { background: #ef4444; width: 33%; }
    .strength-medium { background: #f59e0b; width: 66%; }
    .strength-strong { background: #10b981; width: 100%; }

    .terms-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 1.5rem;
    }

    .terms-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #dc2626;
        margin-top: 2px;
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

    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #ffffff;
        border-radius: 50%;
        border-top-color: transparent;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Hide default header/footer for auth pages */
    .auth-container body {
        background: none;
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
    
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
        <div class="w-full max-w-5xl">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 items-center">
                <!-- Register Form -->
                <div class="auth-card animate-fade-in-up">
                    <div class="auth-form">
                        <!-- Logo -->
                        <div class="text-center mb-6 lg:mb-8">
                            <div class="flex items-center justify-center space-x-3 mb-3 lg:mb-4">
                                <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-red-600 to-red-700 rounded-xl flex items-center justify-center">
                                    <span class="text-white font-bold text-lg lg:text-xl">Y</span>
                                </div>
                                <span class="text-xl lg:text-2xl font-bold text-gradient">Yakan</span>
                            </div>
                            <h2 class="text-xl lg:text-2xl font-bold text-gray-900">Create Account</h2>
                            <p class="text-gray-600 mt-1 lg:mt-2 text-sm lg:text-base">Join Yakan and start your shopping journey</p>
                        </div>

                        <!-- Social Login -->
                        <div class="space-y-3 mb-4 lg:mb-6">
                            <a href="{{ route('auth.redirect', 'google') }}" class="social-login-btn">
                                <svg class="w-5 h-5" viewBox="0 0 24 24">
                                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                                </svg>
                                <span class="text-sm lg:text-base">Sign up with Google</span>
                            </a>
                            
                            <a href="{{ route('auth.redirect', 'facebook') }}" class="social-login-btn">
                                <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                                <span class="text-sm lg:text-base">Sign up with Facebook</span>
                            </a>
                        </div>

                        <div class="divider">
                            <span>OR</span>
                        </div>

                        <!-- TEST: Deployment Check -->
                        <div class="bg-yellow-100 border-2 border-yellow-500 text-yellow-900 px-4 py-3 rounded-lg mb-4 font-bold text-center">
                            ⚠️ ERROR DISPLAY ACTIVE - Version March 8, 2026 @ {{ date('H:i:s') }}<br>
                            DEBUG: Errors count = {{ $errors->count() }} | Has errors: {{ $errors->any() ? 'YES' : 'NO' }}
                        </div>

                        <!-- DEBUG: Force Show All Errors -->
                        @if($errors->count() > 0)
                            <div class="bg-red-100 border-4 border-red-600 text-red-900 px-4 py-3 rounded-lg mb-4">
                                <p class="font-bold text-lg mb-2">🚨 VALIDATION ERRORS DETECTED ({{ $errors->count() }} errors):</p>
                                <ul class="list-disc list-inside space-y-1 font-semibold">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Error/Success Messages -->
                        @if($errors->any())
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="flex-1">
                                        <p class="font-semibold mb-1">Please fix the following errors:</p>
                                        <ul class="list-disc list-inside text-sm space-y-1">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                {{ session('error') }}
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Register Form -->
                        <form method="POST" action="{{ route('register.store') }}" id="registerForm">
                            @csrf
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="input-group">
                                    <input 
                                        id="first_name" 
                                        type="text" 
                                        name="first_name" 
                                        class="auth-input" 
                                        placeholder="First name"
                                        value="{{ old('first_name') }}"
                                        required
                                        autofocus
                                    >
                                    <label for="first_name" class="input-icon">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </label>
                                </div>

                                <div class="input-group">
                                    <input 
                                        id="last_name" 
                                        type="text" 
                                        name="last_name" 
                                        class="auth-input" 
                                        placeholder="Last name"
                                        value="{{ old('last_name') }}"
                                        required
                                    >
                                    <label for="last_name" class="input-icon">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </label>
                                </div>
                            </div>

                            @error('first_name')
                                <div class="error-message">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror

                            @error('last_name')
                                <div class="error-message">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror

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
                                >
                                <label for="email" class="input-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </label>
                            </div>

                            @error('email')
                                <div class="error-message">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="input-group">
                                <input 
                                    id="password" 
                                    type="password" 
                                    name="password" 
                                    class="auth-input" 
                                    placeholder="Password (min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special)"
                                    required
                                    autocomplete="new-password"
                                    oninput="checkPasswordStrength(this.value)"
                                >
                                <label for="password" class="input-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </label>
                                <div class="password-strength">
                                    <div id="password-strength-bar" class="password-strength-bar"></div>
                                </div>
                                <div class="text-xs text-gray-600 mt-1">
                                    Must contain: 8+ chars, 1 uppercase, 1 lowercase, 1 number, 1 special (@$!%*#?&)
                                </div>
                            </div>

                            @error('password')
                                <div class="error-message">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="input-group">
                                <input 
                                    id="password_confirmation" 
                                    type="password" 
                                    name="password_confirmation" 
                                    class="auth-input" 
                                    placeholder="Confirm password"
                                    required
                                    autocomplete="new-password"
                                >
                                <label for="password_confirmation" class="input-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </label>
                            </div>

                            @error('password_confirmation')
                                <div class="error-message">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="terms-checkbox">
                                <input type="checkbox" name="terms" id="terms" required>
                                <label for="terms" class="text-sm text-gray-700">
                                    I agree to the <a href="#" class="text-red-600 hover:text-red-700">Terms of Service</a> and <a href="#" class="text-red-600 hover:text-red-700">Privacy Policy</a>
                                </label>
                            </div>

                            <button 
                                type="submit" 
                                id="submitBtn"
                                class="btn-primary w-full text-lg py-3 flex items-center justify-center gap-2"
                            >
                                <span id="btnText">Create Account</span>
                                <span id="btnLoading" class="hidden items-center gap-2">
                                    <div class="loading-spinner"></div>
                                    Creating Account...
                                </span>
                            </button>
                        </form>

                        <!-- Login Link -->
                        <div class="text-center mt-6 lg:mt-8">
                            <p class="text-gray-600 text-sm lg:text-base">
                                Already have an account? 
                                <a href="{{ route('login') }}" class="text-red-600 hover:text-red-700 font-medium">
                                    Sign in here
                                </a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Illustration Side -->
                <div class="hidden lg:block">
                    <div class="auth-illustration">
                        <div class="w-16 h-16 lg:w-24 lg:h-24 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center mb-6 lg:mb-8">
                            <svg class="w-8 h-8 lg:w-12 lg:h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-2xl lg:text-3xl font-bold mb-3 lg:mb-4">Join the Yakan Family</h3>
                        <p class="text-red-100 mb-6 lg:mb-8 text-base lg:text-lg">
                            Create your account and unlock exclusive benefits and features
                        </p>

                        <ul class="feature-list">
                            <li>Personalized recommendations</li>
                            <li>Exclusive member discounts</li>
                            <li>Early access to new products</li>
                            <li>Save multiple shipping addresses</li>
                            <li>Track order history easily</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let passwordTimeout;
function checkPasswordStrength(password) {
    clearTimeout(passwordTimeout);
    passwordTimeout = setTimeout(() => {
        const strengthBar = document.getElementById('password-strength-bar');
        let strength = 0;
        
        // Check all requirements
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++; // lowercase
        if (password.match(/[A-Z]/)) strength++; // uppercase
        if (password.match(/[0-9]/)) strength++; // number
        if (password.match(/[@$!%*#?&]/)) strength++; // special character
        
        strengthBar.className = 'password-strength-bar';
        
        if (strength <= 2) {
            strengthBar.classList.add('strength-weak');
        } else if (strength <= 4) {
            strengthBar.classList.add('strength-medium');
        } else {
            strengthBar.classList.add('strength-strong');
        }
    }, 300);
}

// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Show loading state
            submitBtn.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            btnLoading.classList.add('flex');
        });
    }
});
</script>
@endsection
