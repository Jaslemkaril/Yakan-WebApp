@extends('layouts.admin')

@section('title', 'Create Intended Use')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Create Intended Use</h1>
        <p class="text-gray-600">Add a new intended use option</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-lg p-8">
        <form action="{{ route('admin.intended_uses.store') }}" method="POST">
            @csrf

            <!-- Name Field -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Intended Use Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2" style="--tw-ring-color: #800000;" placeholder="e.g., Clothing, Home Decor, Crafts" required>
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description Field -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea id="description" name="description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2" style="--tw-ring-color: #800000;" placeholder="Brief description of this intended use">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Field -->
            <div class="mb-8">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-5 h-5" style="accent-color: #800000;">
                    <span class="ml-3 text-sm font-semibold text-gray-700">Active (Visible to customers)</span>
                </label>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="flex-1 px-6 py-3 rounded-lg font-bold text-white transition-all" style="background-color: #800000;" onmouseover="this.style.backgroundColor='#600000'" onmouseout="this.style.backgroundColor='#800000'">
                    Create Intended Use
                </button>
                <a href="{{ route('admin.intended_uses.index') }}" class="flex-1 px-6 py-3 rounded-lg font-bold text-gray-700 border-2 border-gray-300 text-center transition-all hover:border-gray-400">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
