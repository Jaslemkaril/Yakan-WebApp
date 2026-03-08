@extends('layouts.app')

@section('title', 'Forgot Password - Yakan')

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
        padding: 3rem;
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

    .auth-btn {
        width: 100%;
        padding: 14px 24px;
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    .auth-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.4);
        background: linear-gradient(135deg, #b91c1c, #991b1b);
    }

    .auth-btn:active {
        transform: translateY(0);
    }

    .benefits-section {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        color: white;
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .benefit-item {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 1rem;
        font-size: 15px;
    }

    .benefit-icon {
        width: 20px;
        height: 20px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .auth-form {
            padding: 2rem;
        }
        .benefits-section {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="auth-container">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto">
            <div class="grid md:grid-cols-2 gap-0 auth-card">
                <!-- Form Section -->
                <div class="auth-form">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-red-800 to-red-900 rounded-xl flex items-center justify-center">
                                <span class="text-white font-bold text-2xl">Y</span>
                            </div>
                            <h1 class="text-3xl font-bold bg-gradient-to-r from-red-800 to-orange-600 bg-clip-text text-transparent">
                                Yakan
                            </h1>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Forgot Password</h2>
                        <p class="text-gray-600 text-sm mt-2">
                            Enter your account email and we'll send you a link to reset your password.
                        </p>
                    </div>

                    @if (session('status'))
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <p>{{ session('status') }}</p>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <div class="flex-1">
                                    <ul class="text-sm space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="input-group">
                            <input 
                                id="email" 
                                type="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus
                                placeholder="Email address"
                                class="auth-input"
                            >
                            <svg class="input-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>

                        <button type="submit" class="auth-btn mb-4">
                            Email Password Reset Link
                        </button>

                        <div class="text-center">
                            <a href="{{ route('login.user.form') }}" class="text-red-700 hover:text-red-800 font-semibold text-sm transition">
                                ← Back to Login
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Benefits Section -->
                <div class="benefits-section hidden md:flex">
                    <div class="mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-white/10 rounded-2xl mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold mb-2">Secure Account Recovery</h3>
                        <p class="text-white/80 text-sm">
                            We'll send you a secure link to reset your password and regain access to your account.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span>Quick and easy password reset</span>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span>Secure email verification</span>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span>Protected account access</span>
                        </div>
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <span>24/7 support available</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
