<section x-data="{ editing: false }">
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

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
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
            <input 
                id="email" 
                name="email" 
                type="email" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8B1A1A] focus:border-transparent transition-all duration-200 disabled:bg-gray-100 disabled:cursor-not-allowed" 
                value="{{ old('email', $user->email) }}" 
                required 
                autocomplete="username"
                :disabled="!editing"
            />
            @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        {{ __('Your email address is unverified.') }}

                        <button type="submit" form="send-verification" class="underline text-yellow-900 hover:text-yellow-700 font-medium">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm text-green-600 font-medium">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif

                    @if (session('error'))
                        <p class="mt-2 text-sm text-red-600 font-medium">
                            {{ session('error') }}
                        </p>
                    @endif
                </div>
            @endif
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
