@extends('layouts.app')

@section('title', 'Yakan - Premium Products & Custom Orders')

@push('styles')
<style>
    .hero-section {
        background-color: #3a0000;
        position: relative;
        overflow: hidden;
        min-height: 80vh;
        display: flex;
        align-items: center;
        background-size: cover;
        background-position: center center;
        background-repeat: no-repeat;
    }

    /* Subtle Yakan pattern overlay for non-hero sections */
    .yakan-pattern-bg {
        position: relative;
    }
    .yakan-pattern-bg::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: var(--yakan-pattern-url);
        background-size: 320px auto;
        background-repeat: repeat;
        opacity: 0.06;
        pointer-events: none;
        z-index: 0;
    }
    .yakan-pattern-bg > * {
        position: relative;
        z-index: 1;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom, rgba(0,0,0,0.55) 0%, rgba(60,0,0,0.65) 100%);
        pointer-events: none;
    }

    .hero-section::after {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
    }

    .hero-content {
        position: relative;
        z-index: 10;
        text-shadow: 0 2px 12px rgba(0,0,0,0.7), 0 1px 3px rgba(0,0,0,0.9);
    }

    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.25);
        backdrop-filter: blur(10px);
        border-radius: 999px;
        padding: 6px 18px;
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(255,230,230,0.95);
        margin-bottom: 1.5rem;
    }

    .hero-badge .dot {
        width: 6px;
        height: 6px;
        background: #ff6b6b;
        border-radius: 50%;
        animation: pulse-dot 2s ease-in-out infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.4); }
    }

    .hero-divider {
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, rgba(255,255,255,0.8), rgba(255,255,255,0.2));
        border-radius: 999px;
        margin: 1.5rem auto;
    }



    .feature-card {
        background: white;
        border-radius: 24px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    @media (min-width: 768px) {
        .feature-card {
            padding: 2rem;
        }
    }

    .feature-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #dc2626, #880900ff);
    }

    .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .product-showcase {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border-radius: 24px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    
    @media (min-width: 768px) {
        .product-showcase {
            padding: 2.5rem;
        }
    }
    
    @media (min-width: 1024px) {
        .product-showcase {
            padding: 3rem;
        }
    }

    .product-showcase::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(220, 38, 38, 0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .testimonial-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: relative;
    }
    
    @media (min-width: 768px) {
        .testimonial-card {
            padding: 2rem;
        }
    }

    .testimonial-card::before {
        content: '"';
        position: absolute;
        top: 1rem;
        left: 1rem;
        font-size: 4rem;
        color: #dc2626;
        opacity: 0.2;
        font-family: Georgia, serif;
    }

    .cta-section {
        background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
        border-radius: 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: var(--yakan-pattern-url);
        background-size: 300px auto;
        background-repeat: repeat;
        opacity: 0.08;
    }

    .cta-section::after {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 20% 50%, rgba(139, 0, 0, 0.25) 0%, transparent 50%),
            radial-gradient(circle at 80% 50%, rgba(139, 0, 0, 0.25) 0%, transparent 50%);
        pointer-events: none;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .animate-float {
        animation: float 6s ease-in-out infinite;
    }
</style>
@endpush

@section('content')
    <style>:root { --yakan-pattern-url: url('https://raw.githubusercontent.com/Jaslemkaril/Yakan-WebApp/main/public/images/jus.jpg'); }</style>
    <!-- Success Messages -->
    @if(session('success') || session('status'))
        <div id="success-message" class="fixed top-20 right-4 z-50 max-w-sm animate-fade-in-up">
            <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-xl shadow-2xl flex items-center space-x-3">
                <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div>
                    <p class="font-semibold">Success!</p>
                    <p class="text-sm">{{ session('success') ?? session('status') }}</p>
                </div>
            </div>
        </div>
        <script>
            setTimeout(function() {
                const msgEl = document.getElementById('success-message');
                if (msgEl) {
                    msgEl.style.animation = 'fadeOut 0.5s ease-out forwards';
                    setTimeout(() => msgEl.remove(), 500);
                }
            }, 4000);
        </script>
        <style>
            @keyframes fadeOut {
                from { opacity: 1; transform: translateX(0); }
                to { opacity: 0; transform: translateX(100%); }
            }
        </style>
    @endif

    <!-- Hero Section -->
    <section class="hero-section text-white" style="background-image: url('https://raw.githubusercontent.com/Jaslemkaril/Yakan-WebApp/main/public/images/jus.jpg');">
        <div class="hero-content w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 lg:py-36 text-center">

            <!-- Badge -->
            <div class="flex justify-center animate-fade-in-up" style="animation-delay:0s">
                <span class="hero-badge">
                    <span class="dot"></span>
                    Philippine Heritage Craft
                </span>
            </div>

            <!-- Headline -->
            <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold leading-tight tracking-tight text-white drop-shadow-2xl animate-fade-in-up" style="animation-delay:0.1s">
                TUWAS YAKAN
            </h1>

            <!-- Divider -->
            <div class="hero-divider animate-fade-in-up" style="animation-delay:0.2s"></div>

            <!-- Sub-headline -->
            <p class="text-lg sm:text-xl md:text-2xl lg:text-3xl text-red-100 font-semibold tracking-wide animate-fade-in-up" style="animation-delay:0.25s">
                Weaving Through Generations
            </p>

            <!-- Description -->
            <p class="mt-4 text-base sm:text-lg text-red-200 leading-relaxed max-w-2xl mx-auto font-light animate-fade-in-up" style="animation-delay:0.35s">
                Authentic handcrafted products with traditional artistry passed down through generations of skilled Yakan weavers from Basilan, Philippines.
            </p>

            <!-- CTA Buttons -->
            <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center animate-fade-in-up" style="animation-delay:0.45s">
                <a href="{{ route('products.index') }}" class="group inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl text-base font-semibold text-white shadow-2xl transition-all duration-300 hover:scale-105 hover:shadow-red-900/60" style="background: rgba(255,255,255,0.15); border: 1.5px solid rgba(255,255,255,0.35); backdrop-filter: blur(12px);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Shop Products
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="{{ route('custom_orders.index') }}" class="group inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl text-base font-semibold shadow-2xl transition-all duration-300 hover:scale-105" style="background: white; color: #800000; border: 1.5px solid white;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    Custom Orders
                </a>
            </div>

            <!-- Mobile App Download -->
            <div class="mt-6 animate-fade-in-up" style="animation-delay:0.55s">
                <a href="{{ config('app.mobile_apk_url') }}" download
                   class="group inline-flex items-center justify-center gap-3 px-7 py-3.5 rounded-2xl text-sm font-semibold text-white transition-all duration-300 hover:scale-105 hover:brightness-110 shadow-xl"
                   style="background: linear-gradient(135deg, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0.06) 100%); border: 1.5px solid rgba(255,255,255,0.3); backdrop-filter: blur(14px);">
                    <!-- Android robot icon -->
                    <svg class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.523 15.341A5.99 5.99 0 0018 13V9a6 6 0 00-12 0v4a5.99 5.99 0 00.477 2.341L5 17h14l-1.477-1.659zM12 2a1 1 0 100-2 1 1 0 000 2zM7.05 3.343a1 1 0 10-1.414 1.414 1 1 0 001.414-1.414zm10.314 1.414a1 1 0 10-1.414-1.414 1 1 0 001.414 1.414zM4 19h16a1 1 0 000-2H4a1 1 0 000 2zm4 2a2 2 0 104 0H8z"/>
                    </svg>
                    <span>Download Android App</span>
                    <!-- Download arrow -->
                    <svg class="w-4 h-4 group-hover:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-bold" style="background:rgba(255,255,255,0.2); letter-spacing:0.04em">APK</span>
                </a>
                <p class="mt-2 text-xs text-red-200 opacity-70">Free &middot; Android &middot; {{ config('app.mobile_app_version') }}</p>
            </div>

            <!-- Trust pills -->
            <div class="mt-10 flex flex-wrap justify-center gap-3 animate-fade-in-up" style="animation-delay:0.55s">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm text-red-100" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15)">
                    <svg class="w-4 h-4 text-red-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    100% Authentic
                </span>
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm text-red-100" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15)">
                    <svg class="w-4 h-4 text-red-300" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Handcrafted with Pride
                </span>
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm text-red-100" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15)">
                    <svg class="w-4 h-4 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Basilan, Philippines
                </span>
            </div>

        </div>
    </section>

    <!-- Features Section -->
    <section class="py-12 md:py-16 lg:py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 md:mb-16 animate-fade-in-up">
                <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-gradient mb-3 md:mb-4">Why Choose Yakan?</h2>
                <p class="text-base sm:text-lg md:text-xl text-gray-600 max-w-3xl mx-auto px-4">Experience the perfect blend of quality, creativity, and exceptional service</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <div class="feature-card animate-slide-in-left" style="animation-delay: 0.1s">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-orange-500 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Premium Quality</h3>
                    <p class="text-gray-600 leading-relaxed">Every product is carefully selected and quality-checked to ensure you receive only the best items that meet our high standards.</p>
                </div>

                <div class="feature-card animate-slide-in-left" style="animation-delay: 0.2s">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Custom Orders</h3>
                    <p class="text-gray-600 leading-relaxed">Create personalized products tailored to your specific needs. Our team works closely with you to bring your vision to life.</p>
                </div>

                <div class="feature-card animate-slide-in-left" style="animation-delay: 0.3s">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-teal-500 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Fast Delivery</h3>
                    <p class="text-gray-600 leading-relaxed">Quick and reliable delivery service ensures your orders reach you on time. Track your package every step of the way.</p>
                </div>

                <div class="feature-card animate-slide-in-left" style="animation-delay: 0.4s">
                    <div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Secure Payments</h3>
                    <p class="text-gray-600 leading-relaxed">Multiple secure payment options available. Your financial information is protected with industry-standard encryption.</p>
                </div>

                <div class="feature-card animate-slide-in-left" style="animation-delay: 0.5s">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 109.75 9.75A9.75 9.75 0 0012 2.25z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">24/7 Support</h3>
                    <p class="text-gray-600 leading-relaxed">Our dedicated customer support team is always here to help you with any questions or concerns you may have.</p>
                </div>

                <div class="feature-card animate-slide-in-left" style="animation-delay: 0.6s">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-blue-500 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Satisfaction Guaranteed</h3>
                    <p class="text-gray-600 leading-relaxed">We stand behind our products with a satisfaction guarantee. Your happiness is our top priority.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Showcase -->
    <section class="py-12 md:py-16 lg:py-20 bg-gradient-to-b from-white via-red-50/30 to-white yakan-pattern-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="product-showcase relative">
                <div class="text-center mb-8 md:mb-12 relative z-10">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-100 to-orange-100 rounded-full mb-4 backdrop-blur-sm">
                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="text-sm font-semibold text-gray-800">Handpicked Collection</span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-3 md:mb-4">Featured Products</h2>
                    <p class="text-base sm:text-lg md:text-xl text-gray-600 px-4 max-w-2xl mx-auto">Discover our handpicked selection of premium authentic Yakan handwoven items</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8 relative z-10">
                    @foreach(($featuredProducts ?? collect()) as $index => $product)
                        <div class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl overflow-hidden transform hover:-translate-y-2 transition-all duration-500 animate-fade-in-up border border-gray-100" style="animation-delay: {{ $index * 0.1 }}s">
                            <!-- Image Container -->
                            <div class="relative aspect-square bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden">
                                <img src="{{ $product->image_src }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out"
                                     loading="lazy">
                                
                                <!-- Overlay on hover -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-black/0 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                
                                <!-- Quick Actions -->
                                <div class="absolute top-3 right-3 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-x-4 group-hover:translate-x-0">
                                    @auth
                                    <button onclick="toggleWishlist(event, {{ $product->id }})" 
                                            id="wishlist-btn-{{ $product->id }}"
                                            class="w-10 h-10 bg-white/95 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg hover:bg-red-50 hover:text-red-600 transition-colors duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </button>
                                    @endauth
                                    <a href="{{ route('products.show', $product->id) }}" 
                                       class="w-10 h-10 bg-white/95 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                </div>
                                
                                <!-- Stock Badge -->
                                @if(!$product->isInStock())
                                    <div class="absolute top-3 left-3">
                                        <span class="px-3 py-1 bg-red-600 text-white text-xs font-bold rounded-full shadow-lg">
                                            Out of Stock
                                        </span>
                                    </div>
                                @elseif($product->isLowStock())
                                    <div class="absolute top-3 left-3">
                                        <span class="px-3 py-1 text-white text-xs font-bold rounded-full shadow-lg animate-pulse" style="background-color:#800000;">
                                            Low Stock
                                        </span>
                                    </div>
                                @endif
                                
                                <!-- Category Badge (bottom-left, visible on hover) -->
                                @if($product->category)
                                <div class="absolute bottom-3 left-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                    <span class="px-3 py-1 bg-white/95 backdrop-blur-sm text-gray-800 text-xs font-semibold rounded-full shadow-lg">
                                        {{ $product->category->name }}
                                    </span>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Product Info -->
                            <div class="p-5">
                                <!-- Rating -->
                                @if($product->review_count > 0)
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= floor($product->average_rating))
                                                <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-gray-300 fill-current" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="text-xs text-gray-500">({{ $product->review_count }})</span>
                                </div>
                                @endif
                                
                                <!-- Product Name -->
                                <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-red-700 transition-colors duration-200">
                                    {{ $product->name }}
                                </h3>
                                
                                <!-- Description -->
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2 leading-relaxed">
                                    {{ Str::limit($product->description ?? 'Premium quality handwoven Yakan product', 70) }}
                                </p>
                                
                                <!-- Price & CTA -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Price</p>
                                        <span class="text-2xl font-bold bg-gradient-to-r bg-clip-text text-transparent" style="background-image: linear-gradient(to right, #800000, #5f0000);">
                                            ₱{{ number_format($product->price ?? 0) }}
                                        </span>
                                    </div>
                                    <a href="{{ route('products.show', $product->id) }}" 
                                       class="group/btn relative px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-700 text-white text-sm font-semibold rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                                        <span class="relative z-10">View</span>
                                        <div class="absolute inset-0 bg-white opacity-0 group-hover/btn:opacity-20 rounded-xl transition-opacity"></div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-12 relative z-10">
                    <a href="{{ route('products.index') }}" 
                       class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-red-600 to-red-700 text-white text-lg font-semibold rounded-2xl hover:from-red-700 hover:to-red-800 transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 group">
                        <span>View All Products</span>
                        <svg class="w-5 h-5 transform group-hover:translate-x-2 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-12 md:py-16 lg:py-20 bg-gray-50 yakan-pattern-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 md:mb-14">
                <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-gradient mb-3 md:mb-4">What Our Customers Say</h2>
                <p class="text-base sm:text-lg md:text-xl text-gray-600 px-4">Real reviews from verified buyers</p>
            </div>

            @if($testimonials->count() > 0)
                @php
                    $avatarGradients = [
                        'from-red-500 to-orange-500',
                        'from-blue-500 to-purple-500',
                        'from-green-500 to-teal-500',
                        'from-pink-500 to-rose-500',
                        'from-yellow-500 to-amber-500',
                        'from-indigo-500 to-blue-500',
                    ];
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                    @foreach($testimonials as $i => $review)
                        @php
                            $initials = strtoupper(substr($review->user->name ?? 'U', 0, 1) . (strpos($review->user->name ?? '', ' ') !== false ? substr($review->user->name, strpos($review->user->name, ' ') + 1, 1) : ''));
                            $gradient = $avatarGradients[$i % count($avatarGradients)];
                        @endphp
                        <div class="testimonial-card animate-fade-in-up" style="animation-delay: {{ $i * 0.1 }}s">
                            <div class="relative z-10 flex flex-col h-full">
                                {{-- Reviewer Info --}}
                                <div class="flex items-center mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br {{ $gradient }} rounded-full flex items-center justify-center text-white font-bold mr-4 flex-shrink-0 text-sm">
                                        {{ $initials }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-gray-900 truncate">{{ $review->user->name }}</h4>
                                        <div class="flex items-center gap-1">
                                            @for($s = 1; $s <= 5; $s++)
                                                <svg class="w-4 h-4 {{ $s <= $review->rating ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                            <span class="text-xs text-gray-500 ml-1">{{ $review->rating }}/5</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Product Ordered --}}
                                @if($review->product)
                                    <div class="flex items-center gap-2 mb-3 px-3 py-2 bg-gray-50 rounded-xl border border-gray-100">
                                        @if($review->product->hasImage())
                                            <img src="{{ $review->product->image_url }}" alt="{{ $review->product->name }}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0 border border-gray-200">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-500">Ordered</p>
                                            <p class="text-xs font-semibold text-gray-800 truncate">{{ $review->product->name }}</p>
                                        </div>
                                    </div>
                                @elseif($review->customOrder)
                                    <div class="flex items-center gap-2 mb-3 px-3 py-2 bg-gray-50 rounded-xl border border-gray-100">
                                        <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-500">Ordered</p>
                                            <p class="text-xs font-semibold text-gray-800 truncate">
                                                {{ $review->customOrder->product->name ?? $review->customOrder->product_type ?? 'Custom Order' }}
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                {{-- Review comment --}}
                                <p class="text-gray-600 italic text-sm leading-relaxed flex-1">"{{ Str::limit($review->comment, 160) }}"</p>

                                {{-- Date --}}
                                <p class="text-xs text-gray-400 mt-3">{{ $review->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- Empty state: no approved reviews yet --}}
                <div class="text-center py-16">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    </div>
                    <p class="text-gray-500 font-medium">Be the first to leave a review!</p>
                    <p class="text-gray-400 text-sm mt-1">Purchase a product and share your experience.</p>
                    <a href="{{ route('products.index') }}" class="inline-block mt-4 px-6 py-2 rounded-xl text-white text-sm font-semibold hover:opacity-90 transition-opacity" style="background:#800000;">Shop Now</a>
                </div>
            @endif
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-12 md:py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="cta-section text-white p-8 sm:p-10 md:p-12 lg:p-20 relative">
                <div class="relative z-10 text-center">
                    <div class="inline-block mb-4 md:mb-6">
                        <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-red-500 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-3 md:mb-4 shadow-lg">
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-6xl font-bold mb-4 md:mb-6 bg-gradient-to-r from-white via-red-100 to-white bg-clip-text text-transparent px-4">Ready to Start Shopping?</h2>
                    <p class="text-base sm:text-lg md:text-xl lg:text-2xl text-gray-300 mb-8 md:mb-10 max-w-3xl mx-auto leading-relaxed px-4">
                        Join thousands of satisfied customers who have discovered the perfect blend of quality and creativity.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 md:gap-5 justify-center">
                        <a href="{{ route('products.index') }}" class="group relative overflow-hidden bg-white text-gray-900 text-sm sm:text-base md:text-lg px-6 sm:px-8 md:px-10 py-3 sm:py-4 md:py-5 rounded-xl font-bold shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 inline-flex items-center justify-center">
                            <span class="relative z-10">Start Shopping</span>
                            <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                            <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-orange-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <span class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 text-white font-bold transition-opacity duration-300">
                                Start Shopping
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </span>
                        </a>
                        <a href="{{ route('custom_orders.index') }}" class="group bg-transparent border-2 border-white text-white text-sm sm:text-base md:text-lg px-6 sm:px-8 md:px-10 py-3 sm:py-4 md:py-5 rounded-xl font-bold hover:bg-white hover:text-gray-900 shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 inline-flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <span>Create Custom Order</span>
                        </a>
                    </div>
                    <!-- Trust indicators -->
                    <div class="mt-10 md:mt-12 flex flex-wrap items-center justify-center gap-4 sm:gap-6 md:gap-8 text-gray-400 text-xs sm:text-sm px-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Secure Checkout</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                            </svg>
                            <span>Fast Delivery</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            <span>Quality Guaranteed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    const wishlistProductIds = @json($wishlistProductIds ?? []);
    const welcomeWishlistState = new Set();

    document.addEventListener('DOMContentLoaded', function() {
        wishlistProductIds.forEach(function(productId) {
            const numericId = Number(productId);
            welcomeWishlistState.add(numericId);
            setWelcomeWishlistButtonState(numericId, true);
        });
    });

    function setWelcomeWishlistButtonState(productId, inWishlist) {
        const button = document.getElementById(`wishlist-btn-${productId}`);
        if (!button) return;

        const svg = button.querySelector('svg');
        if (!svg) return;

        if (inWishlist) {
            button.classList.add('in-wishlist');
            button.style.color = '#800000';
            svg.setAttribute('fill', '#800000');
            svg.setAttribute('stroke', '#800000');
        } else {
            button.classList.remove('in-wishlist');
            button.style.color = '';
            svg.setAttribute('fill', 'none');
            svg.setAttribute('stroke', 'currentColor');
        }
    }

    // Toggle Wishlist
    function toggleWishlist(event, productId) {
        event.preventDefault();
        event.stopPropagation();

        const button = event.currentTarget;
        if (!button) return;

        const numericProductId = Number(productId);
        const currentlyInWishlist = welcomeWishlistState.has(numericProductId) || button.classList.contains('in-wishlist');
        const action = currentlyInWishlist ? 'remove' : 'add';
        const baseRoute = action === 'add' ? '{{ route("wishlist.add") }}' : '{{ route("wishlist.remove") }}';

        const authToken = localStorage.getItem('yakan_auth_token') || sessionStorage.getItem('auth_token');
        const requestUrl = new URL(baseRoute, window.location.origin);
        if (authToken) {
            requestUrl.searchParams.set('auth_token', authToken);
        }

        button.disabled = true;

        fetch(requestUrl.toString(), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Auth-Token': authToken || ''
            }
            ,
            credentials: 'include',
            body: JSON.stringify({
                type: 'product',
                id: productId,
                auth_token: authToken || undefined
            })
        })
        .then(async response => {
            let data = {};
            try {
                data = await response.json();
            } catch (e) {
                data = { success: false, message: 'Unexpected server response' };
            }

            if (!response.ok) {
                if (response.status === 401) {
                    throw new Error('Please login to add to wishlist');
                }
                throw new Error(data.message || 'Unable to update wishlist');
            }

            return data;
        })
        .then(data => {
            if (data.success) {
                // Visual feedback
                button.classList.add('animate-bounce');
                setTimeout(() => button.classList.remove('animate-bounce'), 600);

                const nextInWishlist = action === 'add';
                if (nextInWishlist) {
                    welcomeWishlistState.add(numericProductId);
                } else {
                    welcomeWishlistState.delete(numericProductId);
                }
                setWelcomeWishlistButtonState(numericProductId, nextInWishlist);
                
                // Show toast notification
                showToast(data.message || 'Wishlist updated!');
            } else {
                showToast(data.message || 'Unable to update wishlist', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast(error.message || 'Error updating wishlist', 'error');
        })
        .finally(() => {
            button.disabled = false;
        });
    }
    
    // Simple toast notification
    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-xl z-50 transform transition-all duration-300 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white font-medium`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.add('translate-y-2', 'opacity-0'), 2500);
        setTimeout(() => toast.remove(), 3000);
    }
</script>
@endpush
