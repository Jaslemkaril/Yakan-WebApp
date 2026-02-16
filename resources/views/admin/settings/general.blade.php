@extends('layouts.admin')

@section('title', 'General Settings')

@section('content')
<div class="space-y-6">
    <!-- Settings Header -->
    <div class="bg-gradient-to-r from-[#800000] to-[#600000] rounded-2xl p-8 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">General Settings</h1>
                <p class="text-gray-100 text-lg">Manage your application configuration</p>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('admin.dashboard') }}" class="bg-white/20 backdrop-blur-sm text-white border border-white/30 rounded-lg px-4 py-2 hover:bg-white/30 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Settings Navigation -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <a href="{{ route('admin.settings.general') }}" class="py-4 px-1 border-b-2 border-[#800000] font-medium text-sm text-[#800000]">
                    <i class="fas fa-cog mr-2"></i>General
                </a>
                <a href="#" class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-envelope mr-2"></i>Email
                </a>
                <a href="#" class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-shield-alt mr-2"></i>Security
                </a>
                <a href="#" class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-paint-brush mr-2"></i>Appearance
                </a>
            </nav>
        </div>

        <!-- General Settings Form -->
        <div class="p-6">
            <form method="POST" action="{{ route('admin.settings.general.update') }}" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Site Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Site Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="site_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Site Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="site_name" 
                                   name="site_name" 
                                   value="{{ old('site_name', $settings['site_name']) }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent"
                                   placeholder="Yakan E-commerce">
                            @error('site_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="site_email" class="block text-sm font-medium text-gray-700 mb-2">
                                Site Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   id="site_email" 
                                   name="site_email" 
                                   value="{{ old('site_email', $settings['site_email']) }}"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent"
                                   placeholder="admin@yakan.com">
                            @error('site_email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="site_description" class="block text-sm font-medium text-gray-700 mb-2">
                            Site Description
                        </label>
                        <textarea id="site_description" 
                                  name="site_description" 
                                  rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent"
                                  placeholder="A brief description of your e-commerce platform">{{ old('site_description') }}</textarea>
                        @error('site_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Application Settings -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Application Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">
                                Timezone
                            </label>
                            <select id="timezone" 
                                    name="timezone" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                                <option value="UTC" {{ old('timezone', config('app.timezone')) == 'UTC' ? 'selected' : '' }}>UTC</option>
                                <option value="Asia/Manila" {{ old('timezone', config('app.timezone')) == 'Asia/Manila' ? 'selected' : '' }}>Asia/Manila (UTC+8)</option>
                                <option value="America/New_York" {{ old('timezone', config('app.timezone')) == 'America/New_York' ? 'selected' : '' }}>America/New_York (UTC-5)</option>
                                <option value="Europe/London" {{ old('timezone', config('app.timezone')) == 'Europe/London' ? 'selected' : '' }}>Europe/London (UTC+0)</option>
                            </select>
                            @error('timezone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="locale" class="block text-sm font-medium text-gray-700 mb-2">
                                Default Language
                            </label>
                            <select id="locale" 
                                    name="locale" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent">
                                <option value="en" {{ old('locale', config('app.locale')) == 'en' ? 'selected' : '' }}>English</option>
                                <option value="es" {{ old('locale', config('app.locale')) == 'es' ? 'selected' : '' }}>Spanish</option>
                                <option value="fr" {{ old('locale', config('app.locale')) == 'fr' ? 'selected' : '' }}>French</option>
                                <option value="de" {{ old('locale', config('app.locale')) == 'de' ? 'selected' : '' }}>German</option>
                            </select>
                            @error('locale')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="maintenance_mode" 
                                   name="maintenance_mode" 
                                   value="1"
                                   {{ old('maintenance_mode', $settings['maintenance_mode']) ? 'checked' : '' }}
                                   class="h-4 w-4 text-[#800000] focus:ring-[#800000] border-gray-300 rounded">
                            <label for="maintenance_mode" class="ml-2 block text-sm text-gray-900">
                                Enable Maintenance Mode
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">When enabled, only administrators can access the application.</p>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Contact Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Contact Phone
                            </label>
                            <input type="tel" 
                                   id="contact_phone" 
                                   name="contact_phone" 
                                   value="{{ old('contact_phone') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent"
                                   placeholder="+63 912 345 6789">
                            @error('contact_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_address" class="block text-sm font-medium text-gray-700 mb-2">
                                Contact Address
                            </label>
                            <input type="text" 
                                   id="contact_address" 
                                   name="contact_address" 
                                   value="{{ old('contact_address') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent"
                                   placeholder="123 Main St, City, Country">
                            @error('contact_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Social Media Links -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Social Media Links</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="facebook_url" class="block text-sm font-medium text-gray-700 mb-2">
                                Facebook URL
                            </label>
                            <input type="url" 
                                   id="facebook_url" 
                                   name="facebook_url" 
                                   value="{{ old('facebook_url') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent"
                                   placeholder="https://facebook.com/yourpage">
                            @error('facebook_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="twitter_url" class="block text-sm font-medium text-gray-700 mb-2">
                                Twitter URL
                            </label>
                            <input type="url" 
                                   id="twitter_url" 
                                   name="twitter_url" 
                                   value="{{ old('twitter_url') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent"
                                   placeholder="https://twitter.com/yourhandle">
                            @error('twitter_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="instagram_url" class="block text-sm font-medium text-gray-700 mb-2">
                                Instagram URL
                            </label>
                            <input type="url" 
                                   id="instagram_url" 
                                   name="instagram_url" 
                                   value="{{ old('instagram_url') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent"
                                   placeholder="https://instagram.com/yourhandle">
                            @error('instagram_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="youtube_url" class="block text-sm font-medium text-gray-700 mb-2">
                                YouTube URL
                            </label>
                            <input type="url" 
                                   id="youtube_url" 
                                   name="youtube_url" 
                                   value="{{ old('youtube_url') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#800000] focus:border-transparent"
                                   placeholder="https://youtube.com/yourchannel">
                            @error('youtube_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Changes will take effect immediately after saving
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-[#800000] text-white rounded-lg hover:bg-[#600000] transition-colors">
                            <i class="fas fa-save mr-2"></i>Save Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- System Information -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">System Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm text-gray-600">Laravel Version</div>
                    <div class="text-lg font-semibold text-gray-900">{{ app()->version() }}</div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm text-gray-600">PHP Version</div>
                    <div class="text-lg font-semibold text-gray-900">{{ PHP_VERSION }}</div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm text-gray-600">Environment</div>
                    <div class="text-lg font-semibold text-gray-900">{{ config('app.env') }}</div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm text-gray-600">Current Timezone</div>
                    <div class="text-lg font-semibold text-gray-900">{{ config('app.timezone') }}</div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm text-gray-600">Debug Mode</div>
                    <div class="text-lg font-semibold text-gray-900">{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</div>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm text-gray-600">Maintenance Mode</div>
                    <div class="text-lg font-semibold text-gray-900">{{ app()->isDownForMaintenance() ? 'Active' : 'Inactive' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
