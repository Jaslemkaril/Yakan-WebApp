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
        max-width: 500px;
        margin: 0 auto;
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

    .otp-input-container {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin: 2rem 0;
    }

    .otp-input {
        width: 50px;
        height: 50px;
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: white;
        transition: all 0.3s ease;
    }

    .otp-input:focus {
        outline: none;
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }

    .otp-input.filled {
        border-color: #10b981;
        background: #f0fdf4;
    }

    .btn-primary {
        background: linear-gradient(135deg, #dc2626 0%, #ea580c 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        width: 100%;
        padding: 14px;
        font-size: 16px;
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

    .btn-secondary {
        background: transparent;
        color: #dc2626;
        border: 2px solid #dc2626;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        width: 100%;
        padding: 12px;
        font-size: 16px;
    }

    .btn-secondary:hover:not(:disabled) {
        background: #dc2626;
        color: white;
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

    .success-message {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .text-gradient {
        background: linear-gradient(135deg, #dc2626 0%, #ea580c 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .countdown {
        font-size: 14px;
        color: #666;
        text-align: center;
        margin-top: 1rem;
    }

    .countdown.expired {
        color: #dc2626;
        font-weight: 600;
    }

    .resend-section {
        text-align: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
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
    
    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md">
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
                        <h2 class="text-2xl font-bold text-gray-900">Verify Your Email</h2>
                        <p class="text-gray-600 mt-2">
                            We've sent a 6-digit code to<br>
                            <strong>{{ $user->email }}</strong>
                        </p>
                    </div>

                    <!-- Error/Success Messages -->
                    @if($errors->any())
                        <div class="error-message">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="success-message">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- OTP Form -->
                    <form method="POST" action="{{ route('verification.otp.verify') }}" id="otpForm">
                        @csrf
                        <input type="hidden" name="email" value="{{ $user->email }}">
                        
                        <div class="text-center mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-4">
                                Enter Verification Code
                            </label>
                            
                            <div class="otp-input-container">
                                <input type="text" class="otp-input" maxlength="1" data-index="0" autocomplete="off">
                                <input type="text" class="otp-input" maxlength="1" data-index="1" autocomplete="off">
                                <input type="text" class="otp-input" maxlength="1" data-index="2" autocomplete="off">
                                <input type="text" class="otp-input" maxlength="1" data-index="3" autocomplete="off">
                                <input type="text" class="otp-input" maxlength="1" data-index="4" autocomplete="off">
                                <input type="text" class="otp-input" maxlength="1" data-index="5" autocomplete="off">
                            </div>
                            
                            <input type="hidden" name="otp" id="otpValue">
                        </div>

                        <button type="submit" class="btn-primary" id="verifyBtn" disabled>
                            Verify Email
                        </button>
                    </form>

                    <!-- Countdown Timer -->
                    <div class="countdown" id="countdown">
                        Code expires in: <span id="timer">10:00</span>
                    </div>

                    <!-- Resend Section -->
                    <div class="resend-section">
                        <p class="text-gray-600 text-sm mb-4">
                            Didn't receive the code?
                        </p>
                        
                        <form method="POST" action="{{ route('verification.otp.resend') }}" id="resendForm">
                            @csrf
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            <button type="submit" class="btn-secondary" id="resendBtn">
                                Send New Code
                            </button>
                        </form>
                        
                        <div class="mt-4">
                            <a href="{{ route('register') }}" class="text-red-600 hover:text-red-700 text-sm">
                                ← Back to Registration
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const otpInputs = document.querySelectorAll('.otp-input');
    const otpValue = document.getElementById('otpValue');
    const verifyBtn = document.getElementById('verifyBtn');
    const countdownEl = document.getElementById('countdown');
    const timerEl = document.getElementById('timer');
    
    // OTP Input handling
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', function(e) {
            const value = e.target.value;
            
            // Only allow numbers
            if (!/^\d$/.test(value)) {
                e.target.value = '';
                return;
            }
            
            // Add filled class
            if (value) {
                e.target.classList.add('filled');
                // Move to next input
                if (index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            } else {
                e.target.classList.remove('filled');
            }
            
            updateOtpValue();
        });
        
        input.addEventListener('keydown', function(e) {
            // Handle backspace
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                otpInputs[index - 1].focus();
                otpInputs[index - 1].value = '';
                otpInputs[index - 1].classList.remove('filled');
                updateOtpValue();
            }
            
            // Handle paste
            if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                navigator.clipboard.readText().then(text => {
                    const digits = text.replace(/\D/g, '').slice(0, 6);
                    digits.split('').forEach((digit, i) => {
                        if (otpInputs[i]) {
                            otpInputs[i].value = digit;
                            otpInputs[i].classList.add('filled');
                        }
                    });
                    updateOtpValue();
                });
            }
        });
    });
    
    function updateOtpValue() {
        const otp = Array.from(otpInputs).map(input => input.value).join('');
        otpValue.value = otp;
        verifyBtn.disabled = otp.length !== 6;
    }
    
    // Countdown timer (10 minutes = 600 seconds)
    let timeLeft = 600;
    
    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        timerEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (timeLeft <= 0) {
            countdownEl.classList.add('expired');
            timerEl.textContent = 'EXPIRED';
            verifyBtn.disabled = true;
            verifyBtn.textContent = 'Code Expired';
        } else {
            timeLeft--;
        }
    }
    
    // Update timer every second
    updateTimer();
    const timerInterval = setInterval(updateTimer, 1000);
    
    // Focus first input
    otpInputs[0].focus();
    
    // Handle form submissions
    document.getElementById('otpForm').addEventListener('submit', function() {
        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<div class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div>Verifying...';
    });
    
    document.getElementById('resendForm').addEventListener('submit', function() {
        const resendBtn = document.getElementById('resendBtn');
        resendBtn.disabled = true;
        resendBtn.innerHTML = '<div class="inline-block w-4 h-4 border-2 border-red-600 border-t-transparent rounded-full animate-spin mr-2"></div>Sending...';
    });
});
</script>
@endsection