<section x-data="{ editing: false, showCurrent: false, showNew: false, showConfirm: false }">
    <form method="post" action="{{ route('password.update') }}{{ request('auth_token') ? '?auth_token=' . urlencode(request('auth_token')) : '' }}" class="space-y-5">
        @csrf
        @method('put')
        @if(request('auth_token'))
            <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
        @endif

        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
            <div class="relative">
                <input 
                    id="update_password_current_password" 
                    name="current_password" 
                    :type="showCurrent ? 'text' : 'password'" 
                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent transition-all duration-200 disabled:bg-gray-100 disabled:cursor-not-allowed" 
                    autocomplete="current-password"
                    :disabled="!editing"
                />
                <button type="button" @click="showCurrent = !showCurrent" x-show="editing"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-[#8B1A1A] transition-colors" tabindex="-1">
                    <svg x-show="!showCurrent" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showCurrent" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            @if($errors->updatePassword->has('current_password'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
            <div class="relative">
                <input 
                    id="update_password_password" 
                    name="password" 
                    :type="showNew ? 'text' : 'password'" 
                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent transition-all duration-200 disabled:bg-gray-100 disabled:cursor-not-allowed" 
                    autocomplete="new-password"
                    :disabled="!editing"
                />
                <button type="button" @click="showNew = !showNew" x-show="editing"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-[#8B1A1A] transition-colors" tabindex="-1">
                    <svg x-show="!showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            @if($errors->updatePassword->has('password'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
            <div class="relative">
                <input 
                    id="update_password_password_confirmation" 
                    name="password_confirmation" 
                    :type="showConfirm ? 'text' : 'password'" 
                    class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent transition-all duration-200 disabled:bg-gray-100 disabled:cursor-not-allowed" 
                    autocomplete="new-password"
                    :disabled="!editing"
                />
                <button type="button" @click="showConfirm = !showConfirm" x-show="editing"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-[#8B1A1A] transition-colors" tabindex="-1">
                    <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            @if($errors->updatePassword->has('password_confirmation'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-4">
            <!-- Change Password Button (shown when not editing) -->
            <button 
                type="button" 
                @click="editing = true" 
                x-show="!editing"
                class="px-6 py-3 bg-[#8B1A1A] hover:bg-[#6B1414] text-white font-medium rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg"
            >
                Change Password
            </button>

            <!-- Cancel and Update Password Buttons (shown when editing) -->
            <div x-show="editing" class="flex items-center gap-3">
                <button 
                    type="button" 
                    @click="editing = false" 
                    class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg"
                >
                    Cancel
                </button>
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-[#8B1A1A] hover:bg-[#6B1414] text-white font-medium rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg"
                >
                    Update Password
                </button>
            </div>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 font-medium flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Password updated successfully!
                </p>
            @endif
        </div>
    </form>
</section>
