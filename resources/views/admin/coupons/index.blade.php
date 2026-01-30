@extends('layouts.admin')

@section('title', 'Coupons Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-[#800000] rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl md:text-3xl font-bold">Coupons Management</h1>
                <p class="text-purple-100 text-lg mt-2">Manage promotional codes and discounts</p>
            </div>
            <a href="{{ route('admin.coupons.create') }}" class="inline-flex items-center px-6 py-3 bg-white text-purple-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                <i class="fas fa-plus mr-2"></i>New Coupon
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Coupons Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Code</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Type</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Value</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Min Spend</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Uses</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono font-semibold text-gray-900 bg-gray-100 px-3 py-1 rounded">{{ $coupon->code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $coupon->type === 'percent' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($coupon->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ $coupon->type === 'percent' ? $coupon->value.'%' : '₱'.number_format($coupon->value,2) }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">₱{{ number_format($coupon->min_spend,2) }}</td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $coupon->times_redeemed }} @if($coupon->usage_limit)/ {{ $coupon->usage_limit }} @endif
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('admin.coupons.toggle', $coupon) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium transition-colors {{ $coupon->active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                                        <i class="fas {{ $coupon->active ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                                        {{ $coupon->active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="inline-flex items-center px-3 py-2 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition-colors text-sm font-medium">
                                        <i class="fas fa-edit mr-1"></i>Edit
                                    </a>
                                    <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Delete this coupon?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-sm font-medium">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <i class="fas fa-ticket-alt text-4xl text-gray-300 mb-4"></i>
                                <p class="text-gray-500 text-lg">No coupons yet. Create one to get started!</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($coupons->hasPages())
            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
