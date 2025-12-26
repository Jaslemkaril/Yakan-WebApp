@extends('layouts.admin')

@section('title', 'Enhanced Dashboard - Yakan Admin')

@push('styles')
<style>
/* Modern Dashboard Styles */
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --dark-gradient: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
}

/* Glass morphism effect */
.glass-effect {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
}

/* Card animations */
.dashboard-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    transform-origin: center;
}

.dashboard-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

/* Gradient text */
.gradient-text {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Loading skeleton */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Pulse animation for live data */
.pulse-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #10b981;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
    100% { opacity: 1; transform: scale(1); }
}

/* Chart container */
.chart-container {
    position: relative;
    height: 300px;
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
}

/* Modern button styles */
.btn-modern {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-primary-modern {
    background: var(--primary-gradient);
    color: white;
}

.btn-primary-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
}

/* Progress bars */
.progress-modern {
    height: 8px;
    border-radius: 4px;
    background: #e5e7eb;
    overflow: hidden;
}

.progress-bar-modern {
    height: 100%;
    background: var(--primary-gradient);
    transition: width 0.6s ease;
}

/* Notification badge */
.notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ef4444;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    animation: pulse 2s infinite;
}

/* Stats grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

/* Activity feed */
.activity-item {
    display: flex;
    align-items: center;
    padding: 16px;
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: #f9fafb;
    border-left-color: #667eea;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    font-size: 16px;
}

/* Quick actions */
.quick-action-card {
    padding: 24px;
    text-align: center;
    border-radius: 12px;
    background: white;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    cursor: pointer;
}

.quick-action-card:hover {
    border-color: #667eea;
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(102, 126, 234, 0.15);
}

.quick-action-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 16px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

/* Responsive design */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-action-card {
        padding: 16px;
    }
    
    .quick-action-icon {
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
}
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8 text-center animate-fade-in-up">
        <h1 class="text-4xl font-bold gradient-text mb-2">Enhanced Dashboard</h1>
        <p class="text-gray-600 text-lg">Real-time insights and analytics for Yakan Ecommerce</p>
        <div class="flex items-center justify-center mt-2">
            <span class="pulse-dot"></span>
            <span class="ml-2 text-sm text-gray-500">Live data</span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <!-- Total Orders -->
        <div class="dashboard-card glass-effect rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-white"></i>
                </div>
                <span class="text-sm text-gray-500">+12.5%</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900" id="totalOrders">{{ $totalOrders ?? 0 }}</h3>
            <p class="text-gray-600 text-sm">Total Orders</p>
            <div class="progress-modern mt-3">
                <div class="progress-bar-modern" style="width: 75%"></div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="dashboard-card glass-effect rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center">
                    <i class="fas fa-peso-sign text-white"></i>
                </div>
                <span class="text-sm text-gray-500">+23.1%</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">₱{{ number_format($totalRevenue ?? 0, 2) }}</h3>
            <p class="text-gray-600 text-sm">Total Revenue</p>
            <div class="progress-modern mt-3">
                <div class="progress-bar-modern" style="width: 85%"></div>
            </div>
        </div>

        <!-- Customers -->
        <div class="dashboard-card glass-effect rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-orange-500 to-red-500 flex items-center justify-center">
                    <i class="fas fa-users text-white"></i>
                </div>
                <span class="text-sm text-gray-500">+8.3%</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900" id="totalCustomers">{{ $totalCustomers ?? 0 }}</h3>
            <p class="text-gray-600 text-sm">Total Customers</p>
            <div class="progress-modern mt-3">
                <div class="progress-bar-modern" style="width: 60%"></div>
            </div>
        </div>

        <!-- Products -->
        <div class="dashboard-card glass-effect rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center">
                    <i class="fas fa-box text-white"></i>
                </div>
                <span class="text-sm text-gray-500">+5.7%</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900" id="totalProducts">{{ $totalProducts ?? 0 }}</h3>
            <p class="text-gray-600 text-sm">Total Products</p>
            <div class="progress-modern mt-3">
                <div class="progress-bar-modern" style="width: 90%"></div>
            </div>
        </div>
    </div>

    <!-- Charts and Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Revenue Chart -->
        <div class="lg:col-span-2">
            <div class="chart-container">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Overview</h3>
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="glass-effect rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
            <div class="space-y-3">
                <div class="activity-item">
                    <div class="activity-icon bg-blue-100 text-blue-600">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">New order #1234</p>
                        <p class="text-xs text-gray-500">2 minutes ago</p>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon bg-green-100 text-green-600">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">New customer registered</p>
                        <p class="text-xs text-gray-500">5 minutes ago</p>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon bg-purple-100 text-purple-600">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">New product review</p>
                        <p class="text-xs text-gray-500">12 minutes ago</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="quick-action-card" onclick="window.location.href='{{ route('admin.products.index') }}'">
            <div class="quick-action-icon" style="background: var(--primary-gradient);">
                <i class="fas fa-plus"></i>
            </div>
            <h4 class="font-semibold text-gray-900">Add Product</h4>
            <p class="text-sm text-gray-600 mt-1">Create new product</p>
        </div>

        <div class="quick-action-card" onclick="window.location.href='{{ route('admin.regular.index') }}'">
            <div class="quick-action-icon" style="background: var(--success-gradient);">
                <i class="fas fa-list"></i>
            </div>
            <h4 class="font-semibold text-gray-900">View Orders</h4>
            <p class="text-sm text-gray-600 mt-1">Manage orders</p>
        </div>

        <div class="quick-action-card" onclick="window.location.href='{{ route('admin.custom-orders.index') }}'">
            <div class="quick-action-icon" style="background: var(--warning-gradient);">
                <i class="fas fa-paint-brush"></i>
            </div>
            <h4 class="font-semibold text-gray-900">Custom Orders</h4>
            <p class="text-sm text-gray-600 mt-1">Design requests</p>
        </div>

        <div class="quick-action-card" onclick="window.location.href='{{ route('admin.users.index') ?? '#' }}'">
            <div class="quick-action-icon" style="background: var(--info-gradient);">
                <i class="fas fa-users"></i>
            </div>
            <h4 class="font-semibold text-gray-900">Customers</h4>
            <p class="text-sm text-gray-600 mt-1">User management</p>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-effect rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Conversion Rate</h3>
            <div class="flex items-center justify-center">
                <div class="text-3xl font-bold text-green-600">3.2%</div>
            </div>
            <div class="progress-modern mt-4">
                <div class="progress-bar-modern" style="width: 32%; background: var(--success-gradient);"></div>
            </div>
            <p class="text-sm text-gray-600 mt-2">+0.5% from last month</p>
        </div>

        <div class="glass-effect rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Avg Order Value</h3>
            <div class="flex items-center justify-center">
                <div class="text-3xl font-bold text-blue-600">₱2,450</div>
            </div>
            <div class="progress-modern mt-4">
                <div class="progress-bar-modern" style="width: 65%;"></div>
            </div>
            <p class="text-sm text-gray-600 mt-2">+12% from last month</p>
        </div>

        <div class="glass-effect rounded-xl p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Satisfaction</h3>
            <div class="flex items-center justify-center">
                <div class="text-3xl font-bold text-purple-600">4.8★</div>
            </div>
            <div class="progress-modern mt-4">
                <div class="progress-bar-modern" style="width: 96%; background: var(--warning-gradient);"></div>
            </div>
            <p class="text-sm text-gray-600 mt-2">Based on 1,234 reviews</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize Revenue Chart
const ctx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Revenue',
            data: [12000, 19000, 15000, 25000, 22000, 30000],
            borderColor: 'rgb(102, 126, 234)',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Simulate real-time updates
setInterval(() => {
    // Update random stat
    const stats = ['totalOrders', 'totalCustomers', 'totalProducts'];
    const randomStat = stats[Math.floor(Math.random() * stats.length)];
    const element = document.getElementById(randomStat);
    if (element) {
        const currentValue = parseInt(element.textContent);
        const newValue = currentValue + Math.floor(Math.random() * 3);
        element.textContent = newValue;
        
        // Add pulse animation
        element.style.animation = 'pulse 0.5s';
        setTimeout(() => {
            element.style.animation = '';
        }, 500);
    }
}, 5000);

// Add smooth scroll behavior
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Add loading states for cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.dashboard-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>
@endpush
