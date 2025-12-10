@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6">

    <h1 class="text-3xl font-bold mb-6">🛍 Shop Products</h1>

    @if($products->count() == 0)
        <p class="text-gray-600">No products available at the moment.</p>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

        @foreach($products as $product)
            <div class="bg-white shadow rounded-lg p-4 hover:scale-105 transform transition">
                <img src="{{ asset('uploads/products/' . $product->image) }}" 
                     class="w-full h-48 object-cover rounded-md">

                <h2 class="text-xl font-semibold mt-3">{{ $product->name }}</h2>
                <p class="text-gray-600 text-sm">{{ Str::limit($product->description, 80) }}</p>

                <p class="text-red-600 font-bold mt-2">₱{{ number_format($product->price, 2) }}</p>

                <form action="{{ route('cart.add', $product->id) }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">
                        Add to Cart
                    </button>
                </form>
            </div>
        @endforeach

    </div>

    <div class="mt-6">
        {{ $products->links() }}
    </div>
</div>
@endsection
