@extends('layouts.app')

@section('content')
@php
    $authToken = request('auth_token') ?? session('auth_token') ?? request()->cookie('auth_token');
@endphp
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">

        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('custom_orders.show', ['order' => $order->id, 'auth_token' => $authToken]) }}" class="inline-flex items-center text-sm font-medium hover:underline mb-4" style="color:#800000;">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Order Details
            </a>
            <h1 class="text-3xl font-extrabold text-gray-900">Edit Custom Order</h1>
            <p class="text-gray-600 mt-1">CO-{{ str_pad((string) $order->id, 5, '0', STR_PAD_LEFT) }}</p>
        </div>

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('custom_orders.update', ['order' => $order->id, 'auth_token' => $authToken]) }}" class="bg-white rounded-2xl shadow-xl p-8">
            @csrf
            @method('PUT')

            <!-- Current Pattern/Design Preview -->
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Current Design</label>
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                    @if($order->design_upload)
                        @php
                            $designPath = $order->design_upload;
                            if (str_starts_with($designPath, 'http://') || str_starts_with($designPath, 'https://') || str_starts_with($designPath, 'data:image')) {
                                $imgSrc = $designPath;
                            } elseif (str_starts_with($designPath, 'storage/')) {
                                $imgSrc = asset($designPath);
                            } else {
                                $imgSrc = asset('storage/' . $designPath);
                            }
                        @endphp
                        <img src="{{ $imgSrc }}" alt="Design" class="w-20 h-20 rounded-lg object-cover border border-gray-200">
                    @elseif(!empty($order->patterns) && is_array($order->patterns))
                        @php
                            $patternModel = is_numeric($order->patterns[0]) ? \App\Models\YakanPattern::find($order->patterns[0]) : \App\Models\YakanPattern::where('name', $order->patterns[0])->first();
                        @endphp
                        @if($patternModel)
                            <div class="w-20 h-20 rounded-lg border border-gray-200 bg-white p-1 flex items-center justify-center overflow-hidden">
                                {!! $patternModel->getSvgContent() !!}
                            </div>
                            <span class="text-sm font-medium text-gray-900">{{ $patternModel->name }}</span>
                        @endif
                    @else
                        <span class="text-sm text-gray-500">No design preview</span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-1">To change the design/pattern, please create a new custom order.</p>
            </div>

            <!-- Quantity -->
            <div class="mb-6">
                <label for="quantity" class="block text-sm font-bold text-gray-700 mb-2">Quantity <span class="text-red-500">*</span></label>
                <input type="number" name="quantity" id="quantity" min="1" value="{{ old('quantity', $order->quantity) }}" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm">
                @error('quantity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Specifications / Notes -->
            <div class="mb-6">
                <label for="specifications" class="block text-sm font-bold text-gray-700 mb-2">Specifications / Notes</label>
                <textarea name="specifications" id="specifications" rows="4" maxlength="2000"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
                          placeholder="Any additional notes or specifications...">{{ old('specifications', $order->specifications) }}</textarea>
                @error('specifications')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Delivery Type -->
            <div class="mb-8">
                <label class="block text-sm font-bold text-gray-700 mb-2">Delivery Type <span class="text-red-500">*</span></label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 px-4 py-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ old('delivery_type', $order->delivery_type ?? 'delivery') === 'delivery' ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        <input type="radio" name="delivery_type" value="delivery" {{ old('delivery_type', $order->delivery_type ?? 'delivery') === 'delivery' ? 'checked' : '' }} class="text-red-700" onchange="this.closest('form').querySelectorAll('label[class*=border]').forEach(l => { l.classList.remove('border-red-500','bg-red-50'); l.classList.add('border-gray-300'); }); this.closest('label').classList.add('border-red-500','bg-red-50'); this.closest('label').classList.remove('border-gray-300');">
                        <span class="text-sm font-medium">Delivery</span>
                    </label>
                    <label class="flex items-center gap-2 px-4 py-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ old('delivery_type', $order->delivery_type ?? 'delivery') === 'pickup' ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
                        <input type="radio" name="delivery_type" value="pickup" {{ old('delivery_type', $order->delivery_type ?? 'delivery') === 'pickup' ? 'checked' : '' }} class="text-red-700" onchange="this.closest('form').querySelectorAll('label[class*=border]').forEach(l => { l.classList.remove('border-red-500','bg-red-50'); l.classList.add('border-gray-300'); }); this.closest('label').classList.add('border-red-500','bg-red-50'); this.closest('label').classList.remove('border-gray-300');">
                        <span class="text-sm font-medium">Store Pickup</span>
                    </label>
                </div>
                @error('delivery_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit -->
            <div class="flex items-center gap-4">
                <button type="submit" class="px-8 py-3 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200" style="background-color:#800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                    Save Changes
                </button>
                <a href="{{ route('custom_orders.show', ['order' => $order->id, 'auth_token' => $authToken]) }}" class="px-8 py-3 bg-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-300 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
