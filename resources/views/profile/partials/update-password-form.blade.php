<section x-data="{ editing: false }">
    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
            <input 
                id="update_password_current_password" 
                name="current_password" 
                type="password" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent transition-all duration-200 disabled:bg-gray-100 disabled:cursor-not-allowed" 
                autocomplete="current-password"
                :disabled="!editing"
            />
            @if($errors->updatePassword->has('current_password'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
            <input 
                id="update_password_password" 
                name="password" 
                type="password" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent transition-all duration-200 disabled:bg-gray-100 disabled:cursor-not-allowed" 
                autocomplete="new-password"
                :disabled="!editing"
            />
            @if($errors->updatePassword->has('password'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
            <input 
                id="update_password_password_confirmation" 
                name="password_confirmation" 
                type="password" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent transition-all duration-200 disabled:bg-gray-100 disabled:cursor-not-allowed" 
                autocomplete="new-password"
                :disabled="!editing"
            />
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
