@extends('layouts.app')

@section('title', 'Yakan - Premium Products & Custom Orders')

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, rgba(139, 0, 0, 0.92) 0%, rgba(100, 0, 0, 0.95) 100%), url('https://images.unsplash.com/photo-1577720643272-265e434f3894?w=1200&h=600&fit=crop') center/cover;
        position: relative;
        overflow: hidden;
        min-height: 100vh;
    }
    
    @media (max-width: 768px) {
        .hero-section {
            min-height: auto;
        }
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="50" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="30" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.4;
    }

    .hero-content {
        position: relative;
        z-index: 10;
    }

    .weaver-image-container {
        position: relative;
        transition: transform 0.5s ease;
    }

    .weaver-image-container:hover {
        transform: scale(1.02);
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
        background: 
            radial-gradient(circle at 20% 50%, rgba(139, 0, 0, 0.15) 0%, transparent 50%),
            radial-gradient(circle at 80% 50%, rgba(139, 0, 0, 0.15) 0%, transparent 50%),
            url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="white" opacity="0.05"/><circle cx="80" cy="80" r="2" fill="white" opacity="0.05"/><circle cx="50" cy="50" r="1" fill="white" opacity="0.05"/></svg>');
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
    <section class="hero-section text-white py-12 sm:py-16 md:py-20 lg:py-32">
        <div class="hero-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 md:gap-12 items-center">
                <div class="animate-fade-in-up space-y-4 md:space-y-6">
                    <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold mb-4 md:mb-6 leading-tight text-white drop-shadow-2xl break-words">
                        TUWAS YAKAN
                    </h1>
                    <p class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl mb-4 md:mb-6 text-red-100 leading-snug md:leading-relaxed font-semibold tracking-wide drop-shadow-lg">
                        Weaving Through Generations
                    </p>
                    <p class="text-base sm:text-lg md:text-xl lg:text-xl xl:text-2xl mb-6 md:mb-8 text-red-100 leading-relaxed font-light max-w-xl">
                        Authentic handcrafted products with traditional artistry passed down through generations of skilled Yakan weavers
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 md:gap-4 pt-2 md:pt-4">
                        <a href="{{ route('products.index') }}" class="group bg-maroon-800 hover:bg-maroon-900 text-white text-sm sm:text-base md:text-lg px-6 sm:px-8 md:px-10 py-3 sm:py-4 md:py-5 inline-flex items-center justify-center shadow-2xl hover:shadow-red-500/50 transform hover:scale-105 transition-all duration-300 rounded-lg font-semibold" style="background-color: #800000;">
                            <svg class="w-5 h-5 md:w-6 md:h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span>Shop Products</span>
                            <svg class="w-4 h-4 md:w-5 md:h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        <a href="{{ route('custom_orders.index') }}" class="group btn-secondary text-sm sm:text-base md:text-lg px-6 sm:px-8 md:px-10 py-3 sm:py-4 md:py-5 inline-flex items-center justify-center shadow-2xl border-2 hover:shadow-white/30 transform hover:scale-105 transition-all duration-300">
                            <svg class="w-5 h-5 md:w-6 md:h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <span>Custom Orders</span>
                        </a>
                    </div>
                    
                    <!-- Stats or features -->
                    <div class="grid grid-cols-3 gap-3 sm:gap-4 md:gap-6 pt-6 md:pt-8 border-t border-white/20">
                        <div class="text-center">
                            <div class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-1">1000+</div>
                            <div class="text-xs sm:text-sm text-red-200">Happy Customers</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-1">500+</div>
                            <div class="text-xs sm:text-sm text-red-200">Products</div>
                        </div>
                        <div class="text-center">
                            <div class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-1">100%</div>
                            <div class="text-xs sm:text-sm text-red-200">Authentic</div>
                        </div>
                    </div>
                </div>
                
                <div class="relative animate-float">
                    <div class="bg-white/10 backdrop-blur-md rounded-3xl p-6 border border-white/30 shadow-2xl overflow-hidden weaver-image-container">
                        <div class="aspect-square rounded-2xl flex items-center justify-center overflow-hidden relative bg-gradient-to-br from-red-700 to-red-900 shadow-inner">
                            <!-- Yakan weaver with traditional loom image -->
                            <img src="{{ asset('images/yakan-weaver.webp') }}" alt="Yakan Weaver" class="w-full h-full object-cover" loading="lazy">
                            <!-- Decorative corner accent -->
                            <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-bl from-white/20 to-transparent"></div>
                            <div class="absolute bottom-0 left-0 w-20 h-20 bg-gradient-to-tr from-white/20 to-transparent"></div>
                        </div>
                        <!-- Traditional pattern decoration -->
                        <div class="absolute -top-4 -right-4 w-24 h-24 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full opacity-20 blur-xl"></div>
                        <div class="absolute -bottom-4 -left-4 w-24 h-24 bg-gradient-to-br from-red-400 to-pink-500 rounded-full opacity-20 blur-xl"></div>
                    </div>
                </div>
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
    <section class="py-12 md:py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="product-showcase relative">
                <div class="text-center mb-8 md:mb-12 relative z-10">
                    <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-3 md:mb-4">Featured Products</h2>
                    <p class="text-base sm:text-lg md:text-xl text-gray-700 px-4">Discover our handpicked selection of premium items</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8 relative z-10">
                    @php
                        $featuredProducts = \App\Models\Product::inRandomOrder()->take(4)->get();
                    @endphp
                    
                    @foreach($featuredProducts as $index => $product)
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden transform hover:scale-105 transition-all duration-300 animate-fade-in-up" style="animation-delay: {{ $index * 0.1 }}s">
                            <div class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                @if($product->image)
                                    <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}" 
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="text-6xl">📦</div>
                                @endif
                            </div>
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $product->name }}</h3>
                                <p class="text-gray-600 mb-4">{{ Str::limit($product->description ?? 'Premium quality product', 80) }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-2xl font-bold text-red-600">₱{{ number_format($product->price ?? 0) }}</span>
                                    <a href="{{ route('products.show', $product->id) }}" class="btn-primary px-6 py-2">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-12 relative z-10">
                    <a href="{{ route('products.index') }}" class="btn-primary text-lg px-8 py-4 inline-flex items-center">
                        <span>View All Products</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-12 md:py-16 lg:py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 md:mb-16">
                <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-gradient mb-3 md:mb-4">What Our Customers Say</h2>
                <p class="text-base sm:text-lg md:text-xl text-gray-600 px-4">Real experiences from real customers</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <div class="testimonial-card animate-fade-in-up" style="animation-delay: 0.1s">
                    <div class="relative z-10">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-orange-500 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                JD
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">John Doe</h4>
                                <div class="flex text-yellow-400">
                                    ★★★★★
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 italic">"Amazing quality products and exceptional customer service. The custom order process was smooth and the final product exceeded my expectations!"</p>
                    </div>
                </div>

                <div class="testimonial-card animate-fade-in-up" style="animation-delay: 0.2s">
                    <div class="relative z-10">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                JS
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">Jane Smith</h4>
                                <div class="flex text-yellow-400">
                                    ★★★★★
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 italic">"I love the variety of products available. The website is easy to navigate and the delivery was faster than expected. Highly recommend!"</p>
                    </div>
                </div>

                <div class="testimonial-card animate-fade-in-up" style="animation-delay: 0.3s">
                    <div class="relative z-10">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-teal-500 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                MJ
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">Mike Johnson</h4>
                                <div class="flex text-yellow-400">
                                    ★★★★★
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 italic">"The custom order feature is fantastic! I got exactly what I wanted and the team was very helpful throughout the process."</p>
                    </div>
                </div>
            </div>
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
