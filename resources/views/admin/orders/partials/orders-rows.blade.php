@foreach($orders as $order)
<tr class="hover:bg-gray-100">
    <td class="py-3 px-4">{{ $order->id }}</td>
    <td class="py-3 px-4">{{ $order->user->name ?? 'Guest' }}</td>
    <td class="py-3 px-4">
        @foreach($order->items as $item)
            {{ $item->product->name }} x {{ $item->quantity }} (₱{{ number_format($item->price, 2) }})<br>
        @endforeach
    </td>
    <td class="py-3 px-4">₱{{ number_format($order->total, 2) }}</td>

    <!-- Status -->
    <td class="py-3 px-4">
        <form action="{{ route('admin.orders.quickUpdateStatus', $order->id) }}" method="POST">
            @csrf
            <select name="status" class="border rounded px-2 py-1 w-full" onchange="this.form.submit()">
                <option value="pending" {{ $order->status=='pending'?'selected':'' }}>Pending</option>
                <option value="processing" {{ $order->status=='processing'?'selected':'' }}>Processing</option>
                <option value="completed" {{ $order->status=='completed'?'selected':'' }}>Completed</option>
                <option value="cancelled" {{ $order->status=='cancelled'?'selected':'' }}>Cancelled</option>
            </select>
        </form>
    </td>

    <!-- Payment Status -->
    <td class="py-3 px-4">
        <span class="px-2 py-1 rounded
            @if($order->payment_status=='paid') bg-green-600 text-white
            @elseif($order->payment_status=='pending') bg-yellow-500 text-white
            @elseif($order->payment_status=='refunded') bg-purple-600 text-white
            @else bg-red-600 text-white @endif">
            {{ ucfirst($order->payment_status) }}
        </span>
    </td>

    <!-- Actions -->
    <td class="py-3 px-4 flex space-x-2">
        <a href="{{ route('admin.orders.show', $order->id) }}" class="bg-[#800000] text-white px-3 py-1 rounded hover:bg-[#600000]">
            View
        </a>

        @if ($order->payment_status === 'paid' && $order->status !== 'cancelled')
        <form action="{{ route('admin.orders.refund', $order->id) }}" method="POST" onsubmit="return confirm('Refund this order?');">
            @csrf
            @method('PUT')
            <button class="bg-[#800000] text-white px-3 py-1 rounded hover:bg-[#600000]">
                Refund
            </button>
        </form>
        @endif
    </td>
</tr>
@endforeach
