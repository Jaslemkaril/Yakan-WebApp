@extends('layouts.app')

@section('title', 'Verify Email - Yakan')

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

    .btn-primary {
        background: linear-gradient(135deg, #dc2626 0%, #ea580c 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        padding: 12px 24px;
    }

    .btn-primary:hover:not(:disabled) {
        background: linear-gradient(135deg, #b91c1c 0%, #c2410c 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(220, 38, 38, 0.3);
    }

    .btn-secondary {
        background: transparent;
        color: #dc2626;
        border: 2px solid #dc2626;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        padding: 10px 22px;
    }

    .btn-secondary:hover {
        background: #dc2626;
        color: white;
    }

    .text-gradient {
        background: linear-gradient(135deg, #dc2626 0%, #ea580c 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .success-message {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .info-message {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
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
        <div class="w-full max-w-md">
            <div class="auth-card">
                <div class="auth-form">
                    <!-- Logo -->
                    <div class="text-center mb-6">
                        <div class="flex items-center justify-center space-x-3 mb-4">
                            <img src="{{ asset('images/YakanLogo.png') }}" alt="Yakan Logo" class="w-12 h-12 object-contain">
                            <span class="text-2xl font-bold text-gradient">Yakan</span>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Verify Your Email</h2>
                        <p class="text-gray-600 mt-2">Complete your registration</p>
                    </div>

                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="success-message">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Verification Link Sent Message -->
                    @if (session('status') == 'verification-link-sent')
                        <div class="success-message">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            A new verification link has been sent to your email address.
                        </div>
                    @endif

                    <!-- Info Message -->
                    <div class="info-message">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-medium">Thanks for signing up!</p>
                            <p class="text-sm mt-1">We've sent a verification link to your email. Please check your inbox and click the link to activate your account.</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn-primary w-full">
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Resend Verification Email
                                </span>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-secondary w-full">
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Log Out
                                </span>
                            </button>
                        </form>
                    </div>

                    <!-- Help Text -->
                    <div class="text-center mt-6">
                        <p class="text-sm text-gray-600">
                            Didn't receive the email? Check your spam folder or click "Resend Verification Email".
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
