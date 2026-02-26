@extends('admin.layouts.app')

@section('title', 'Design Selection - Admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">{{ isset($product) ? 'Customize Product' : 'Pattern Selection' }}</h1>
                    <span class="ml-3 px-2 py-1 text-xs font-medium bg-red-50 text-[#800000] rounded-full">Step 2: Design</span>
                </div>
                <a href="{{ route('admin.custom_orders.create.choice') }}" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @php
            $isFabric = isset($isFabricFlow) && $isFabricFlow;
            $formAction = $isFabric
                ? route('admin.custom_orders.store.pattern')
                : route('admin.custom_orders.store.product.customization');
        @endphp

        <form action="{{ $formAction }}" method="POST" class="space-y-8">
            @csrf

            @if(isset($product))
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Product</h3>
                    <div class="text-sm text-gray-600">{{ $product->name }} @if($product->price) • ₱{{ number_format($product->price, 2) }} @endif</div>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Choose a Yakan Pattern</h3>
                    <p class="text-sm text-gray-500 mt-1">Select a traditional Yakan weaving pattern for your custom order</p>
                </div>
                
                @if(isset($patterns) && $patterns->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                        @foreach($patterns as $pattern)
                            @php
                                $primaryMedia = $pattern->media->first();
                                $imageUrl = $primaryMedia 
                                    ? asset('storage/' . $primaryMedia->file_path) 
                                    : asset('images/pattern-placeholder.png');
                            @endphp
                            <label class="block group cursor-pointer transition-transform hover:scale-105">
                                <input type="radio" name="pattern" value="{{ $pattern->id }}" class="sr-only pattern-radio" required>
                                <div class="border-2 border-gray-200 rounded-xl overflow-hidden transition-all duration-200 group-hover:border-[#800000] group-hover:shadow-lg">
                                    <div class="aspect-square bg-gray-100">
                                        <img src="{{ $imageUrl }}" alt="{{ $pattern->name }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="p-3 bg-white">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $pattern->name }}</p>
                                        @if($pattern->category)
                                            <p class="text-xs text-gray-500 mt-1">{{ ucfirst($pattern->category) }}</p>
                                        @endif
                                        @if($pattern->difficulty_level)
                                            <span class="inline-block mt-2 px-2 py-1 text-xs rounded-full
                                                {{ $pattern->difficulty_level === 'simple' ? 'bg-green-100 text-green-700' : '' }}
                                                {{ $pattern->difficulty_level === 'moderate' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                {{ $pattern->difficulty_level === 'complex' ? 'bg-red-100 text-red-700' : '' }}">
                                                {{ ucfirst($pattern->difficulty_level) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 bg-yellow-50 border-2 border-yellow-200 rounded-xl text-center">
                        <svg class="w-12 h-12 text-yellow-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-yellow-800 font-medium">No patterns available</p>
                        <p class="text-yellow-700 text-sm mt-1">Please add Yakan patterns in the admin panel first.</p>
                    </div>
                @endif
                @error('pattern')
                    <p class="text-red-500 text-sm mt-3 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Colors</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color *</label>
                        <input type="color" name="colors[]" value="#B22222" class="w-16 h-10 p-0 border rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Accent Color</label>
                        <input type="color" name="colors[]" value="#2E8B57" class="w-16 h-10 p-0 border rounded">
                    </div>
                    @unless($isFabric)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity *</label>
                            <input type="number" name="quantity" value="1" min="1" class="w-32 px-3 py-2 border rounded">
                        </div>
                    @endunless
                </div>
                @error('colors')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
                @error('quantity')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 border rounded" placeholder="Any extra details..."></textarea>
            </div>

            <div class="flex justify-between items-center">
                <a href="{{ route('admin.custom_orders.create.choice') }}" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors font-medium">← Back</a>
                <button type="submit" class="px-6 py-3 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors font-medium">Continue</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('click', function(e) {
        const card = e.target.closest('label');
        if (!card) return;
        document.querySelectorAll('.pattern-radio').forEach(r => r.closest('label').querySelector('div').classList.remove('border-[#800000]'));
        const radio = card.querySelector('.pattern-radio');
        radio.checked = true;
        card.querySelector('div').classList.add('border-[#800000]');
    });
</script>
@endpush
@endsection
