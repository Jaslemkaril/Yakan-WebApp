<section x-data="{ editing: false }">
    <form id="send-verification" method="post" action="{{ route('verification.send') }}{{ request('auth_token') ? '?auth_token=' . urlencode(request('auth_token')) : '' }}">
        @csrf
        @if(request('auth_token'))
            <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
        @endif
    </form>

    <form method="post" action="{{ route('profile.update') }}{{ request('auth_token') ? '?auth_token=' . urlencode(request('auth_token')) : '' }}" class="space-y-5">
        @csrf
        @method('patch')
        @if(request('auth_token'))
            <input type="hidden" name="auth_token" value="{{ request('auth_token') }}">
        @endif

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name</label>
            <input 
                id="name" 
                name="name" 
                type="text" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent transition-all duration-200 disabled:bg-gray-100 disabled:cursor-not-allowed" 
                value="{{ old('name', $user->name) }}" 
                required 
                autofocus 
                autocomplete="name"
                :disabled="!editing"
            />
            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
            <div class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-50 text-gray-500 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span class="text-sm">{{ $user->email }}</span>
            </div>
            <p class="mt-1.5 text-xs text-gray-400">Email address cannot be changed.</p>
        </div>

        <div class="flex items-center gap-4 pt-4">
            <!-- Edit Profile Button (shown when not editing) -->
            <button 
                type="button" 
                @click="editing = true" 
                x-show="!editing"
                class="px-6 py-3 bg-[#8B1A1A] hover:bg-[#6B1414] text-white font-medium rounded-lg transition-colors duration-200 shadow-md hover:shadow-lg"
            >
                Edit Profile
            </button>

            <!-- Cancel and Save Changes Buttons (shown when editing) -->
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
                    Save Changes
                </button>
            </div>

            @if (session('status') === 'profile-updated')
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
                    Saved successfully!
                </p>
            @endif
        </div>
    </form>
</section>
