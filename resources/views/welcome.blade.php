@extends('layouts.app')

@section('title', 'Yakan - Premium Products & Custom Orders')

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, rgba(128, 0, 0, 0.85) 0%, rgba(128, 0, 0, 0.85) 100%), url('https://images.unsplash.com/photo-1577720643272-265e434f3894?w=1200&h=600&fit=crop') center/cover;
        position: relative;
        overflow: hidden;
    }

    .hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="50" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="30" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        opacity: 0.3;
    }

    .hero-content {
        position: relative;
        z-index: 10;
    }

    .feature-card {
        background: white;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
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
        padding: 3rem;
        position: relative;
        overflow: hidden;
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
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: relative;
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
        background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
        border-radius: 24px;
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="white" opacity="0.05"/><circle cx="80" cy="80" r="2" fill="white" opacity="0.05"/><circle cx="50" cy="50" r="1" fill="white" opacity="0.05"/></svg>');
    }
</style>
@endpush

@section('content')
    <!-- Success Messages -->
    @if(session('success') || session('status'))
        <div class="fixed top-20 right-4 z-50 max-w-sm animate-fade-in-up">
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
    @endif

    <!-- Hero Section -->
    <section class="hero-section text-white py-20 lg:py-32">
        <div class="hero-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="animate-fade-in-up">
                    <h1 class="text-5xl lg:text-7xl font-bold mb-6 leading-tight text-white drop-shadow-lg">
                        TUWAS YAKAN
                    </h1>
                    <p class="text-2xl lg:text-4xl mb-6 text-red-100 leading-relaxed font-semibold tracking-wide">
                        Weaving Through Generations
                    </p>
                    <p class="text-lg lg:text-2xl mb-8 text-red-100 leading-relaxed font-light">
                        Authentic handcrafted products with traditional artistry
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('products.index') }}" class="btn-primary text-lg px-8 py-4 inline-flex items-center justify-center group">
                            <span>Shop Products</span>
                            <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        <a href="{{ route('custom_orders.index') }}" class="btn-secondary text-lg px-8 py-4 inline-flex items-center justify-center">
                            <span>Custom Orders</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </a>
                    </div>
                    
                                    </div>
                
                <div class="relative animate-float">
                    <div class="bg-white/10 backdrop-blur-md rounded-3xl p-8 border border-white/20 shadow-2xl overflow-hidden">
                        <div class="aspect-square rounded-2xl flex items-center justify-center overflow-hidden relative bg-gradient-to-br from-red-600 to-red-900">
                            <!-- Yakan weaver with traditional loom image -->
                            <img src="{{ asset('uploads/yakan-weaver.jpg') }}" alt="Yakan Weaver" class="w-full h-full object-cover" loading="lazy">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-fade-in-up">
                <h2 class="text-4xl lg:text-5xl font-bold text-gradient mb-4">Why Choose Yakan?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Experience the perfect blend of quality, creativity, and exceptional service</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
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
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="product-showcase relative">
                <div class="text-center mb-12 relative z-10">
                    <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Featured Products</h2>
                    <p class="text-xl text-gray-700">Discover our handpicked selection of premium items</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative z-10">
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
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-bold text-gradient mb-4">What Our Customers Say</h2>
                <p class="text-xl text-gray-600">Real experiences from real customers</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
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
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="cta-section text-white p-12 lg:p-16 relative">
                <div class="relative z-10 text-center">
                    <h2 class="text-4xl lg:text-5xl font-bold mb-6">Ready to Start Shopping?</h2>
                    <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                        Join thousands of satisfied customers who have discovered the perfect blend of quality and creativity.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('products.index') }}" class="btn-primary text-lg px-8 py-4 bg-white text-gray-900 hover:bg-gray-100">
                            Start Shopping
                        </a>
                        <a href="{{ route('custom_orders.index') }}" class="btn-secondary text-lg px-8 py-4 border-white text-white hover:bg-white hover:text-gray-900">
                            Create Custom Order
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
