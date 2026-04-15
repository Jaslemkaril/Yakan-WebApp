@extends('layouts.admin')

@section('title', 'Create User')

@section('content')
<div class="space-y-6">
    <!-- Create User Header -->
    <div class="bg-gradient-to-r from-maroon-700 to-maroon-900 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Create New User</h1>
                <p class="text-maroon-100 text-lg">Add a new user to the system with appropriate permissions</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('admin.users.index') }}" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Users
                </a>
            </div>
        </div>
    </div>

    <!-- Create User Form -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6">
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-8">
                @csrf
                
                <!-- User Information -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 flex items-center">
                        <i class="fas fa-user text-maroon-600 mr-2"></i>
                        User Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent transition-colors"
                                   placeholder="John Doe">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}"
                                   required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent transition-colors"
                                   placeholder="john.doe@example.com">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Phone Number
                            </label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ old('phone') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent transition-colors"
                                   placeholder="+63 912 345 6789">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                Address
                            </label>
                            <input type="text" 
                                   id="address" 
                                   name="address" 
                                   value="{{ old('address') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent transition-colors"
                                   placeholder="123 Main St, City, Country">
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 flex items-center">
                        <i class="fas fa-cog text-maroon-600 mr-2"></i>
                        Account Settings
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent transition-colors"
                                       placeholder="Enter password">
                                <button type="button" id="togglePassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <div class="text-xs text-gray-500">Password strength:</div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div id="passwordStrength" class="h-2 rounded-full transition-all duration-300"></div>
                                </div>
                            </div>
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent transition-colors"
                                       placeholder="Confirm password">
                                <button type="button" id="toggleConfirmPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                                User Role <span class="text-red-500">*</span>
                            </label>
                            <select id="role" 
                                    name="role" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent transition-colors">
                                <option value="">Select a role</option>
                                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>
                                    👤 User - Can place orders and manage account
                                </option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>
                                    👑 Admin - Full system access
                                </option>
                                <option value="order_staff" {{ old('role') == 'order_staff' ? 'selected' : '' }}>
                                    🧾 Order Staff - Process orders and confirm refunds
                                </option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Account Status <span class="text-red-500">*</span>
                            </label>
                            <select id="status" 
                                    name="status" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent transition-colors">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                    🟢 Active - User can login and use the system
                                </option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                    🔴 Inactive - User cannot login
                                </option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 flex items-center">
                        <i class="fas fa-info-circle text-maroon-600 mr-2"></i>
                        Additional Information
                    </h3>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes
                        </label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon-500 focus:border-transparent transition-colors resize-none"
                                  placeholder="Add any additional notes about this user...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- User Preview -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-eye text-maroon-600 mr-2"></i>
                        User Preview
                    </h3>
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-maroon-100 rounded-full flex items-center justify-center">
                            <span id="previewInitial" class="text-maroon-700 font-bold text-xl">?</span>
                        </div>
                        <div>
                            <div class="text-lg font-medium text-gray-900">
                                <span id="previewName">User Name</span>
                            </div>
                            <div class="text-sm text-gray-500">
                                <span id="previewEmail">user@example.com</span>
                            </div>
                            <div class="flex items-center space-x-2 mt-1">
                                <span id="previewRole" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    User
                                </span>
                                <span id="previewStatus" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        All fields marked with * are required
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.users.index') }}" 
                           class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-3 bg-maroon-700 text-white rounded-lg hover:bg-maroon-800 transition-colors font-medium">
                            <i class="fas fa-user-plus mr-2"></i>Create User
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    toggleConfirmPassword.addEventListener('click', function() {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    // Password strength indicator
    const passwordStrength = document.getElementById('passwordStrength');
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        const strengthBar = document.getElementById('passwordStrength');
        const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500', 'bg-green-600'];
        const widths = ['20%', '40%', '60%', '80%', '100%'];
        
        strengthBar.className = `h-2 rounded-full transition-all duration-300 ${colors[strength] || 'bg-gray-300'}`;
        strengthBar.style.width = widths[strength] || '0%';
    });
    
    // Live preview
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const roleSelect = document.getElementById('role');
    const statusSelect = document.getElementById('status');
    
    const previewName = document.getElementById('previewName');
    const previewEmail = document.getElementById('previewEmail');
    const previewRole = document.getElementById('previewRole');
    const previewStatus = document.getElementById('previewStatus');
    const previewInitial = document.getElementById('previewInitial');
    
    function updatePreview() {
        previewName.textContent = nameInput.value || 'User Name';
        previewEmail.textContent = emailInput.value || 'user@example.com';
        previewInitial.textContent = nameInput.value ? nameInput.value.charAt(0).toUpperCase() : '?';
        
        // Update role badge
        const roleText = roleSelect.options[roleSelect.selectedIndex]?.text.split(' - ')[0] || 'User';
        previewRole.textContent = roleText;
        previewRole.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
            roleSelect.value === 'admin'
                ? 'bg-maroon-100 text-maroon-800'
                : roleSelect.value === 'order_staff'
                    ? 'bg-amber-100 text-amber-800'
                    : 'bg-gray-100 text-gray-800'
        }`;
        
        // Update status badge
        const statusText = statusSelect.options[statusSelect.selectedIndex]?.text.split(' - ')[0] || 'Active';
        previewStatus.textContent = statusText;
        previewStatus.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${
            statusSelect.value === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
        }`;
    }
    
    nameInput.addEventListener('input', updatePreview);
    emailInput.addEventListener('input', updatePreview);
    roleSelect.addEventListener('change', updatePreview);
    statusSelect.addEventListener('change', updatePreview);
    
    updatePreview();
});
</script>

<style>
.bg-maroon-700 { background-color: #800000; }
.bg-maroon-800 { background-color: #660000; }
.bg-maroon-900 { background-color: #4d0000; }
.bg-maroon-100 { background-color: #ffebeb; }
.text-maroon-700 { color: #800000; }
.text-maroon-800 { color: #660000; }
.text-maroon-900 { color: #4d0000; }
.text-maroon-600 { color: #990000; }
.text-maroon-100 { color: #ffebeb; }
.border-maroon-500 { border-color: #800000; }
.ring-maroon-500 { --tw-ring-color: #800000; }
.hover\:bg-maroon-800:hover { background-color: #660000; }
.hover\:bg-maroon-50:hover { background-color: #ffebeb; }
.hover\:text-maroon-900:hover { color: #4d0000; }
</style>
@endsection
