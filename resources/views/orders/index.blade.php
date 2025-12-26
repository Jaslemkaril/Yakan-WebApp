@extends('layouts.app')

@section('content')
<div style="min-height: 100vh; background-color: #f9fafb; padding: 2rem 1rem;">
    <div style="max-width: 1280px; margin: 0 auto;">
        <!-- Header -->
        <div style="margin-bottom: 3rem;">
            <div style="display: flex; flex-direction: column; gap: 1.5rem; align-items: flex-start;">
                <div>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="padding: 0.75rem; background: linear-gradient(135deg, #800000 0%, #600000 100%); border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            <svg style="width: 2rem; height: 2rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <h1 style="font-size: 2rem; font-weight: bold; color: #111827;">My Orders</h1>
                            <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem;">{{ $orders->count() }} order{{ $orders->count() !== 1 ? 's' : '' }} total</p>
                        </div>
                    </div>
                    <p style="font-size: 0.875rem; color: #6b7280;">Track and manage all your orders in one place</p>
                </div>
                <a href="{{ route('products.index') }}" style="display: inline-flex; align-items: center; padding: 0.75rem 2rem; background: linear-gradient(135deg, #800000 0%, #600000 100%); color: white; font-weight: bold; border-radius: 0.75rem; text-decoration: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.2s;">
                    <svg style="width: 1.25rem; height: 1.25rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8m0 8l-9-2m9 2l9-2m-9-8l9 2m-9-2l-9 2"/>
                    </svg>
                    Continue Shopping
                </a>
            </div>
        </div>

        @if($orders->isEmpty())
            <!-- Empty State -->
            <div style="background: white; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 3rem; text-align: center; border: 1px solid #e5e7eb;">
                <div style="display: inline-block; padding: 1.5rem; background: linear-gradient(135deg, #f5e6e6 0%, #e8cccc 100%); border-radius: 50%; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                    <svg style="width: 5rem; height: 5rem; color: #800000;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <h3 style="font-size: 1.875rem; font-weight: bold; color: #111827; margin-bottom: 1rem;">No Orders Yet</h3>
                <p style="font-size: 1rem; color: #6b7280; margin-bottom: 0.75rem;">You haven't placed any orders yet.</p>
                <p style="font-size: 0.875rem; color: #9ca3af; margin-bottom: 2.5rem;">Start shopping now and discover amazing products!</p>
                <a href="{{ route('products.index') }}" style="display: inline-flex; align-items: center; padding: 1rem 2rem; background: linear-gradient(135deg, #800000 0%, #600000 100%); color: white; font-weight: bold; border-radius: 0.75rem; text-decoration: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <svg style="width: 1.25rem; height: 1.25rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8m0 8l-9-2m9 2l9-2m-9-8l9 2m-9-2l-9 2"/>
                    </svg>
                    Start Shopping
                </a>
            </div>
        @else
            <!-- Orders List -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                @foreach($orders as $order)
                    <div style="background: white; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; overflow: hidden; transition: all 0.3s;">
                        <div style="padding: 1.5rem;">
                            <!-- Order Header -->
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; gap: 1rem;">
                                <div>
                                    <h3 style="font-size: 1.125rem; font-weight: bold; color: #111827;">{{ $order->order_ref }}</h3>
                                    <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem;">{{ $order->created_at->format('M d, Y') }}</p>
                                </div>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: flex-end;">
                                    <span style="display: inline-flex; align-items: center; padding: 0.375rem 0.75rem; background: linear-gradient(135deg, #f5e6e6 0%, #e8cccc 100%); color: #800000; font-size: 0.75rem; font-weight: bold; border-radius: 9999px; border: 1px solid #d4a5a5;">
                                        @if($order->status === 'delivered')
                                            ✅ Delivered
                                        @elseif($order->status === 'shipped')
                                            🚚 Shipped
                                        @elseif($order->status === 'processing' || $order->status === 'confirmed')
                                            ⚙️ Processing
                                        @elseif($order->status === 'cancelled')
                                            ❌ Cancelled
                                        @else
                                            ⏳ Pending
                                        @endif
                                    </span>
                                    <span style="display: inline-flex; align-items: center; padding: 0.375rem 0.75rem; background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); color: #166534; font-size: 0.75rem; font-weight: bold; border-radius: 9999px; border: 1px solid #86efac;">
                                        @if($order->payment_status === 'paid' || $order->payment_status === 'verified')
                                            💚 Paid
                                        @elseif($order->payment_status === 'pending')
                                            ⏳ Pending
                                        @else
                                            ❌ Failed
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <!-- Product Images -->
                            <div style="display: flex; gap: 0.5rem; overflow-x: auto; margin-bottom: 1.5rem; padding-bottom: 0.5rem;">
                                @foreach($order->orderItems->take(3) as $item)
                                    <div style="flex-shrink: 0; width: 5rem; height: 5rem; background-color: #f3f4f6; border-radius: 0.75rem; overflow: hidden; border: 2px solid #e5e7eb;">
                                        @if($item->product && $item->product->image)
                                            <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%);">
                                                <svg style="width: 1.5rem; height: 1.5rem; color: #9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                                @if($order->orderItems->count() > 3)
                                    <div style="flex-shrink: 0; width: 5rem; height: 5rem; background: linear-gradient(135deg, #f5e6e6 0%, #e8cccc 100%); border-radius: 0.75rem; border: 2px solid #d4a5a5; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                        <span style="font-size: 0.75rem; font-weight: bold; color: #800000;">+{{ $order->orderItems->count() - 3 }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Items Info -->
                            <div style="background-color: #f9fafb; border-radius: 0.75rem; padding: 1rem; border: 1px solid #e5e7eb; margin-bottom: 1.5rem;">
                                <p style="font-size: 0.75rem; font-weight: bold; color: #374151; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Items ({{ $order->orderItems->count() }})</p>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                    @foreach($order->orderItems->take(2) as $item)
                                        <span style="display: inline-block; padding: 0.375rem 0.75rem; border-radius: 0.5rem; background-color: white; color: #800000; font-size: 0.75rem; font-weight: 600; border: 1px solid #d4a5a5; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">
                                            {{ substr($item->product->name ?? 'Product', 0, 20) }}{{ strlen($item->product->name ?? 'Product') > 20 ? '...' : '' }}
                                        </span>
                                    @endforeach
                                    @if($order->orderItems->count() > 2)
                                        <span style="display: inline-block; padding: 0.375rem 0.75rem; border-radius: 0.5rem; background-color: white; color: #800000; font-size: 0.75rem; font-weight: 600; border: 1px solid #d4a5a5;">
                                            +{{ $order->orderItems->count() - 2 }} more
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Price and Action -->
                            <div style="border-top: 2px solid #e5e7eb; padding-top: 1.25rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                    <span style="font-size: 0.875rem; font-weight: 600; color: #6b7280;">Total Amount</span>
                                    <span style="font-size: 1.5rem; font-weight: bold; color: #800000;">₱{{ number_format($order->total_amount ?? $order->total, 2) }}</span>
                                </div>
                                <a href="{{ route('orders.show', $order->id) }}" style="display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 0.75rem; background: linear-gradient(135deg, #800000 0%, #600000 100%); color: white; font-size: 0.875rem; font-weight: bold; border-radius: 0.75rem; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.2s;">
                                    <svg style="width: 1rem; height: 1rem; margin-right: 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
                <div style="margin-top: 3rem; display: flex; justify-content: center;">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
