@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                        <span class="text-4xl">🧪</span>
                        Payment Sandbox
                    </h1>
                    <p class="text-gray-600 mt-2">Test payment flows in a safe environment</p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full font-semibold">
                        Sandbox Mode Active
                    </span>
                    <button onclick="generateTestData()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                        Generate Test Data
                    </button>
                    <button onclick="clearSandboxData()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                        Clear Data
                    </button>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Test Scenarios -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <span>🎭</span>
                        Test Scenarios
                    </h2>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach($scenarios as $key => $scenario)
                        <div class="border border-gray-200 rounded-xl p-4 hover:border-blue-300 transition-colors">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $scenario['name'] }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $scenario['description'] }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full font-medium 
                                    @if($scenario['status'] == 'paid') bg-green-100 text-green-800
                                    @elseif($scenario['status'] == 'failed') bg-red-100 text-red-800
                                    @elseif($scenario['status'] == 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($scenario['status'] == 'fraud_review') bg-orange-100 text-orange-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $scenario['status'] }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm text-gray-500">
                                <span>⏱️ {{ $scenario['delay'] }}s delay</span>
                                <button onclick="testScenario('{{ $key }}')" class="text-blue-600 hover:text-blue-800 font-medium">
                                    Test →
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="bg-white rounded-2xl shadow-xl p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <span>💳</span>
                        Payment Methods
                    </h2>
                    
                    <div class="space-y-4">
                        <!-- GCash -->
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <span class="text-blue-600 font-bold">G</span>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">GCash</h3>
                                        <p class="text-sm text-gray-600">Mobile wallet payments</p>
                                    </div>
                                </div>
                                <button onclick="testPayment('gcash')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                                    Test GCash
                                </button>
                            </div>
                            <div class="text-sm text-gray-500">
                                <div>📱 Payment URL: {{ $urls['gcash']['payment_url'] }}</div>
                                <div>🔄 Webhook: {{ $urls['gcash']['webhook_url'] }}</div>
                            </div>
                        </div>

                        <!-- Online Banking -->
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <span class="text-green-600 font-bold">B</span>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Online Banking</h3>
                                        <p class="text-sm text-gray-600">Direct bank transfers</p>
                                    </div>
                                </div>
                                <button onclick="testPayment('online_banking')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                                    Test Banking
                                </button>
                            </div>
                            <div class="text-sm text-gray-500">
                                <div>🏦 Payment URL: {{ $urls['online_banking']['payment_url'] }}</div>
                                <div>🔄 Webhook: {{ $urls['online_banking']['webhook_url'] }}</div>
                            </div>
                        </div>

                        <!-- Bank Transfer -->
                        <div class="border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <span class="text-purple-600 font-bold">T</span>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">Bank Transfer</h3>
                                        <p class="text-sm text-gray-600">Manual bank deposits</p>
                                    </div>
                                </div>
                                <button onclick="testBankTransfer()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors">
                                    Test Transfer
                                </button>
                            </div>
                            <div class="text-sm text-gray-500">
                                <div>📋 Instructions: {{ $urls['bank_transfer']['instructions_url'] }}</div>
                                <div>✅ Verification: {{ $urls['bank_transfer']['verification_url'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Test Orders -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <span>📋</span>
                        Recent Test Orders
                    </h2>
                    
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @if($recent_orders->count() > 0)
                            @foreach($recent_orders as $order)
                            <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-gray-900">{{ $order->order_ref ?? '#'.$order->id }}</span>
                                    <span class="text-xs px-2 py-1 rounded-full font-medium
                                        @if($order->payment_status == 'paid') bg-green-100 text-green-800
                                        @elseif($order->payment_status == 'failed') bg-red-100 text-red-800
                                        @elseif($order->payment_status == 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $order->payment_status }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <div>💰 ₱{{ number_format($order->final_price, 2) }}</div>
                                    <div>💳 {{ strtoupper($order->payment_method) }}</div>
                                    <div>📝 {{ $order->product_name }}</div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <div class="text-4xl mb-3">📭</div>
                                <p>No test orders yet</p>
                                <p class="text-sm mt-2">Generate test data to get started</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Cards Info -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <span>💳</span>
                Test Card Numbers
            </h2>
            
            <div class="grid md:grid-cols-3 gap-4">
                <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                    <h3 class="font-semibold text-green-900 mb-2">✅ Success Card</h3>
                    <div class="font-mono text-sm">4111 1111 1111 1111</div>
                    <div class="text-sm text-gray-600 mt-1">Expiry: 12/25 | CVV: 123</div>
                </div>
                
                <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                    <h3 class="font-semibold text-red-900 mb-2">❌ Fail Card</h3>
                    <div class="font-mono text-sm">4000 0000 0000 0002</div>
                    <div class="text-sm text-gray-600 mt-1">Expiry: 12/25 | CVV: 123</div>
                </div>
                
                <div class="border border-orange-200 rounded-lg p-4 bg-orange-50">
                    <h3 class="font-semibold text-orange-900 mb-2">⚠️ Fraud Card</h3>
                    <div class="font-mono text-sm">4100 0000 0000 0001</div>
                    <div class="text-sm text-gray-600 mt-1">Expiry: 12/25 | CVV: 123</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function testScenario(scenario) {
    // Implementation for testing specific scenarios
    console.log('Testing scenario:', scenario);
    showNotification(`Testing ${scenario} scenario...`, 'info');
}

function testPayment(method) {
    // Implementation for testing payment methods
    console.log('Testing payment method:', method);
    showNotification(`Testing ${method} payment...`, 'info');
}

function testBankTransfer() {
    // Implementation for testing bank transfer
    console.log('Testing bank transfer...');
    showNotification('Testing bank transfer...', 'info');
}

function generateTestData() {
    fetch('{{ route("payment.sandbox.generate-data") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Test data generated successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('Failed to generate test data', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

function clearSandboxData() {
    if (!confirm('Are you sure you want to clear all sandbox data?')) return;
    
    fetch('{{ route("payment.sandbox.clear") }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Cleared ${data.deleted_count} test orders`, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showNotification('Failed to clear data', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    });
}

function showNotification(message, type = 'info') {
    // Simple notification implementation
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${
        type === 'success' ? 'bg-green-600' : 
        type === 'error' ? 'bg-red-600' : 
        'bg-blue-600'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>
@endsection
