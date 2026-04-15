<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Staff Login - Yakan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .input-focus:focus {
            border-color: #8b1d1d !important;
            box-shadow: 0 0 0 3px rgba(139, 29, 29, 0.15) !important;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="background: linear-gradient(160deg, #2b2b2b 0%, #4b1d1d 45%, #1f1f1f 100%);">
    <div class="w-full max-w-5xl grid lg:grid-cols-2 gap-8 items-center">
        <div class="w-full max-w-md mx-auto">
            <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10 border border-gray-100">
                <div class="text-center mb-8">
                    <div class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center mb-4" style="background: linear-gradient(to bottom right, #8b1d1d, #6f1717);">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5 6h4m5 5H5a2 2 0 01-2-2V7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Order Staff Access</h1>
                    <p class="text-gray-600 mt-2">Sign in to process orders and refunds</p>
                </div>

                @if($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('staff.login.submit') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Staff Email</label>
                        <input type="email"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               class="input-focus w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none transition-all"
                               placeholder="staff@yakan.com">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               class="input-focus w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none transition-all"
                               placeholder="••••••••">
                    </div>

                    <button type="submit"
                            class="w-full text-white py-3 rounded-xl font-semibold transition-all"
                            style="background: linear-gradient(to right, #8b1d1d, #6f1717);">
                        Login as Order Staff
                    </button>
                </form>

                <div class="mt-6 text-center text-sm">
                    <a href="{{ route('admin.login.form') }}" class="text-[#8b1d1d] font-semibold hover:opacity-80">Admin? Login here</a>
                    <span class="text-gray-400 mx-2">|</span>
                    <a href="{{ route('login.user.form') }}" class="text-[#8b1d1d] font-semibold hover:opacity-80">Customer login</a>
                </div>
            </div>
        </div>

        <div class="hidden lg:block">
            <div class="rounded-3xl p-12 text-white shadow-2xl" style="background: linear-gradient(135deg, #8b1d1d 0%, #4b1d1d 100%);">
                <div class="inline-block px-4 py-2 bg-white/15 rounded-full text-sm font-semibold mb-4">ORDER STAFF PORTAL</div>
                <h2 class="text-4xl font-bold mb-4">Operations Desk</h2>
                <p class="text-red-100 text-lg mb-8">Designed for day-to-day order handling while admin keeps full platform control.</p>
                <ul class="space-y-3 text-base">
                    <li class="bg-white/10 rounded-lg px-4 py-3">Process and update order statuses</li>
                    <li class="bg-white/10 rounded-lg px-4 py-3">Confirm and track refunds</li>
                    <li class="bg-white/10 rounded-lg px-4 py-3">Manage regular and custom order queues</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
