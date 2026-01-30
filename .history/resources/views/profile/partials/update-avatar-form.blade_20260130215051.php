<div class="space-y-6">
    <div class="flex flex-col gap-6">
        <!-- Current Avatar Display -->
        <div class="flex items-center gap-6">
            <div class="flex-shrink-0">
                @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-24 h-24 rounded-full object-cover border-4 border-maroon-600 shadow-lg">
                @else
                    <div class="w-24 h-24 rounded-full flex items-center justify-center border-4 border-maroon-600 shadow-lg" style="background: linear-gradient(to bottom right, #800000, #600000);">
                        <span class="text-white text-3xl font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    </div>
                @endif
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ auth()->user()->name }}</h3>
                <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                <p class="text-xs text-gray-400 mt-1">
                    @if(auth()->user()->provider)
                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full capitalize">{{ auth()->user()->provider }}</span>
                    @else
                        <span class="inline-block px-3 py-1 bg-gray-100 text-gray-800 rounded-full">Local Account</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- Upload Form -->
        <form action="{{ route('profile.avatar.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 hover:border-maroon-600 hover:bg-maroon-50 transition-colors duration-200">
                <div class="flex flex-col items-center justify-center gap-3">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div class="text-center">
                        <label for="avatar" class="cursor-pointer">
                            <span class="text-sm font-semibold text-maroon-600 hover:text-maroon-700">Click to upload</span>
                            <span class="text-sm text-gray-500"> or drag and drop</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to 5MB</p>
                </div>
                <input 
                    type="file" 
                    id="avatar" 
                    name="avatar" 
                    accept="image/*"
                    class="hidden"
                    onchange="this.form.submit();"
                >
            </div>

            @error('avatar')
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-red-700">{{ $message }}</p>
                </div>
            @enderror
        </form>

        <!-- Delete Avatar Button (if avatar exists) -->
        @if(auth()->user()->avatar)
            <form action="{{ route('profile.avatar.delete') }}" method="POST" class="flex items-center gap-4">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-6 py-2 bg-red-50 hover:bg-red-100 text-red-700 font-semibold rounded-lg transition-colors duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Remove Picture
                </button>
            </form>
        @endif
    </div>

    <!-- Status Messages -->
    @if(session('status') === 'avatar-updated')
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-sm text-green-700 font-semibold">✓ Profile picture updated successfully!</p>
        </div>
    @elseif(session('status') === 'avatar-deleted')
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-sm text-green-700 font-semibold">✓ Profile picture removed successfully!</p>
        </div>
    @endif

    <!-- Info Box -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex gap-3">
        <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div class="text-sm text-blue-800">
            <p class="font-semibold">Profile Picture Tips</p>
            <ul class="mt-2 space-y-1 text-xs">
                <li>• Use a clear, recognizable photo</li>
                <li>• Supported formats: JPG, PNG, GIF</li>
                <li>• Maximum file size: 5MB</li>
                <li>• Images will be automatically cropped to square</li>
            </ul>
        </div>
    </div>
</div>
