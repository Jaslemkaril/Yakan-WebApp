@extends('layouts.app')

@section('title', 'Step 2: Design Your Product - Custom Order')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-purple-50">
    <!-- Enhanced Progress Bar -->
    <div class="bg-white shadow-md sm:shadow-lg sticky top-0 z-40 border-b border-purple-100">
        <div class="container mx-auto px-2 sm:px-4 py-2 sm:py-4">
            <div class="flex items-center justify-center space-x-1 sm:space-x-2 lg:space-x-4">
                <div class="flex items-center">
                    <div class="w-4 h-4 sm:w-6 sm:h-6 lg:w-8 lg:h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-xs sm:text-xs lg:text-sm font-semibold shadow">
                        ✓
                    </div>
                    <span class="ml-1 sm:ml-2 text-xs font-medium text-green-600 hidden sm:inline">{{ $isProductFlow ?? false ? 'Product' : 'Fabric' }}</span>
                </div>
                <div class="w-4 sm:w-8 lg:w-16 h-1 bg-green-600 rounded-full"></div>
                <div class="flex items-center">
                    <div class="w-4 h-4 sm:w-6 sm:h-6 lg:w-8 lg:h-8 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-full flex items-center justify-center text-xs sm:text-xs lg:text-sm font-semibold shadow animate-pulse">
                        2
                    </div>
                    <span class="ml-1 sm:ml-2 text-xs font-medium text-purple-600 hidden sm:inline">Design</span>
                </div>
                <div class="w-4 sm:w-8 lg:w-16 h-1 bg-gray-300 rounded-full"></div>
                <div class="flex items-center">
                    <div class="w-4 h-4 sm:w-6 sm:h-6 lg:w-8 lg:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-xs lg:text-sm font-semibold">
                        3
                    </div>
                    <span class="ml-1 sm:ml-2 text-xs text-gray-500 hidden sm:inline">Details</span>
                </div>
                <div class="w-4 sm:w-8 lg:w-16 h-1 bg-gray-300 rounded-full"></div>
                <div class="flex items-center">
                    <div class="w-4 h-4 sm:w-6 sm:h-6 lg:w-8 lg:h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-xs sm:text-xs lg:text-sm font-semibold">
                        4
                    </div>
                    <span class="ml-1 sm:ml-2 text-xs text-gray-500 hidden sm:inline">Review</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Header -->
    <div class="lg:hidden bg-white shadow-lg sm:shadow-xl border-b border-purple-100 sticky top-16 z-30">
        <div class="p-2 sm:p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2 sm:space-x-3">
                    @if($product && $product->image)
                        <img src="{{ asset('uploads/products/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 object-cover rounded-lg sm:rounded-xl border-2 border-purple-200 shadow-md">
                    @else
                        <div class="w-10 h-10 sm:w-12 sm:h-12 lg:w-14 lg:h-14 bg-gradient-to-br from-purple-600 to-blue-600 rounded-lg sm:rounded-xl flex items-center justify-center shadow-md">
                            <span class="text-white font-bold text-sm sm:text-base lg:text-lg">{{ $product ? substr($product->name, 0, 1) : 'Y' }}</span>
                        </div>
                    @endif
                    <div>
                        <h2 class="text-sm sm:text-base lg:text-lg font-bold text-gray-900">{{ $product ? $product->name : 'Custom Yakan Design' }}</h2>
                        <p class="text-xs sm:text-sm text-purple-600 font-semibold">{{ $product ? '₱' . number_format($product->price, 2) : 'Custom Design' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex flex-col lg:flex-row min-h-screen pt-16 lg:pt-0">
        <!-- Pattern Selection Area -->
        <div class="flex-1 bg-gray-50 p-4 lg:p-8">
            <div class="bg-white rounded-2xl shadow-2xl h-full">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">Choose Your Patterns</h3>
                    <p class="text-gray-600 mt-1">Select patterns to add them to the live preview</p>
                    @if(isset($selectedPattern) && $selectedPattern)
                        <div class="mt-3 p-3 bg-purple-50 border border-purple-200 rounded-lg flex items-center gap-3">
                            @php($media = $selectedPattern->media->first())
                            @if($media)
                                <img src="{{ asset('storage/' . $media->path) }}" alt="{{ $selectedPattern->name }}" class="w-10 h-10 rounded object-cover">
                            @endif
                            <div>
                                <div class="text-sm text-purple-900"><strong>Preselected pattern:</strong> {{ $selectedPattern->name }}</div>
                                @if($selectedPattern->difficulty_level)
                                    <div class="text-xs text-gray-600">Difficulty: {{ ucfirst($selectedPattern->difficulty_level) }}</div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                
                <!-- Pattern Grid -->
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <!-- Sussuh Pattern -->
                        <div class="pattern-card group relative bg-white border-2 border-gray-200 rounded-xl p-5 cursor-pointer hover:border-purple-500 hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-2" onclick="selectPattern('sussuh')" data-pattern="sussuh">
                            <div class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <span class="bg-gradient-to-r from-purple-600 to-blue-600 text-white text-xs px-2 py-1 rounded-full font-semibold">Click to Add</span>
                            </div>
                            <div class="relative overflow-hidden rounded-lg mb-3">
                                <svg id="preview-sussuh" class="w-full h-24 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 transition-all duration-300 group-hover:from-purple-50 group-hover:to-blue-50" viewBox="0 0 100 100">
                                    <!-- Woven Sussuh Diamond Pattern with Pixel Grid Effect -->
                                    <defs>
                                        <pattern id="sussuhPattern-preview" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                                            <!-- Thick border grid -->
                                            <rect width="50" height="50" fill="none" stroke="#333333" stroke-width="2"/>
                                            
                                            <!-- Pixelated diamond shape - stitched effect -->
                                            <!-- Top point -->
                                            <rect x="23" y="2" width="4" height="4" fill="currentColor"/>
                                            <rect x="21" y="6" width="8" height="4" fill="currentColor"/>
                                            <rect x="19" y="10" width="12" height="4" fill="currentColor"/>
                                            
                                            <!-- Middle section -->
                                            <rect x="17" y="14" width="16" height="4" fill="currentColor"/>
                                            <rect x="15" y="18" width="20" height="4" fill="currentColor"/>
                                            <rect x="13" y="22" width="24" height="4" fill="currentColor"/>
                                            <rect x="11" y="26" width="28" height="4" fill="currentColor"/>
                                            <rect x="9" y="30" width="32" height="4" fill="currentColor"/>
                                            <rect x="7" y="34" width="36" height="4" fill="currentColor"/>
                                            
                                            <!-- Bottom half -->
                                            <rect x="9" y="38" width="32" height="4" fill="currentColor"/>
                                            <rect x="11" y="42" width="28" height="4" fill="currentColor"/>
                                            <rect x="13" y="46" width="24" height="4" fill="currentColor"/>
                                            
                                            <!-- Inner diamond - accent -->
                                            <rect x="21" y="18" width="8" height="4" fill="#DAA520"/>
                                            <rect x="19" y="22" width="12" height="4" fill="#DAA520"/>
                                            <rect x="17" y="26" width="16" height="4" fill="#DAA520"/>
                                            <rect x="19" y="30" width="12" height="4" fill="#DAA520"/>
                                            <rect x="21" y="34" width="8" height="4" fill="#DAA520"/>
                                            
                                            <!-- Center dot -->
                                            <rect x="23" y="26" width="4" height="4" fill="currentColor"/>
                                            
                                            <!-- Corner accents - woven texture -->
                                            <rect x="23" y="10" width="4" height="4" fill="#DAA520"/>
                                            <rect x="38" y="25" width="4" height="4" fill="#DAA520"/>
                                            <rect x="23" y="40" width="4" height="4" fill="#DAA520"/>
                                            <rect x="8" y="25" width="4" height="4" fill="#DAA520"/>
                                        </pattern>
                                    </defs>
                                    <rect width="100" height="100" fill="url(#sussuhPattern-preview)" color="#B22222"/>
                                </svg>
                                <div class="absolute inset-0 bg-gradient-to-t from-purple-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                            <h4 class="text-sm font-bold text-gray-900 text-center mb-1 group-hover:text-purple-600 transition-colors">Sussuh</h4>
                            <p class="text-xs text-gray-600 text-center">Traditional Diamond</p>
                            <div class="mt-2 flex justify-center space-x-1">
                                <span class="w-2 h-2 bg-purple-400 rounded-full group-hover:animate-pulse"></span>
                                <span class="w-2 h-2 bg-blue-400 rounded-full group-hover:animate-pulse" style="animation-delay: 0.2s"></span>
                                <span class="w-2 h-2 bg-purple-400 rounded-full group-hover:animate-pulse" style="animation-delay: 0.4s"></span>
                            </div>
                        </div>

                        <!-- Banga Pattern -->
                        <div class="pattern-card group relative bg-white border-2 border-gray-200 rounded-xl p-5 cursor-pointer hover:border-purple-500 hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-2" onclick="selectPattern('banga')" data-pattern="banga">
                            <div class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <span class="bg-gradient-to-r from-purple-600 to-blue-600 text-white text-xs px-2 py-1 rounded-full font-semibold">Click to Add</span>
                            </div>
                            <div class="relative overflow-hidden rounded-lg mb-3">
                                <svg id="preview-banga" class="w-full h-24 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 transition-all duration-300 group-hover:from-purple-50 group-hover:to-blue-50" viewBox="0 0 100 100">
                                    <!-- Woven Banga Circular Pattern with Pixel Grid Effect -->
                                    <defs>
                                        <pattern id="bangaPattern-preview" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                                            <!-- Thick border grid -->
                                            <rect width="50" height="50" fill="none" stroke="#333333" stroke-width="2"/>
                                            
                                            <!-- Pixelated circular pattern - woven effect -->
                                            <!-- Outer ring -->
                                            <rect x="5" y="22" width="4" height="6" fill="currentColor"/>
                                            <rect x="6" y="18" width="4" height="4" fill="currentColor"/>
                                            <rect x="8" y="15" width="6" height="3" fill="currentColor"/>
                                            <rect x="12" y="13" width="8" height="2" fill="currentColor"/>
                                            <rect x="18" y="12" width="14" height="2" fill="currentColor"/>
                                            <rect x="30" y="13" width="8" height="2" fill="currentColor"/>
                                            <rect x="36" y="15" width="6" height="3" fill="currentColor"/>
                                            <rect x="40" y="18" width="4" height="4" fill="currentColor"/>
                                            <rect x="41" y="22" width="4" height="6" fill="currentColor"/>
                                            
                                            <!-- Right side -->
                                            <rect x="41" y="28" width="4" height="6" fill="currentColor"/>
                                            <rect x="40" y="32" width="4" height="4" fill="currentColor"/>
                                            <rect x="36" y="35" width="6" height="3" fill="currentColor"/>
                                            <rect x="30" y="37" width="8" height="2" fill="currentColor"/>
                                            <rect x="18" y="38" width="14" height="2" fill="currentColor"/>
                                            <rect x="12" y="37" width="8" height="2" fill="currentColor"/>
                                            <rect x="8" y="35" width="6" height="3" fill="currentColor"/>
                                            <rect x="6" y="32" width="4" height="4" fill="currentColor"/>
                                            <rect x="5" y="28" width="4" height="6" fill="currentColor"/>
                                            
                                            <!-- Inner ring - accent -->
                                            <rect x="15" y="22" width="4" height="6" fill="#DAA520"/>
                                            <rect x="16" y="19" width="4" height="3" fill="#DAA520"/>
                                            <rect x="18" y="17" width="6" height="2" fill="#DAA520"/>
                                            <rect x="22" y="16" width="6" height="2" fill="#DAA520"/>
                                            <rect x="26" y="17" width="6" height="2" fill="#DAA520"/>
                                            <rect x="30" y="19" width="4" height="3" fill="#DAA520"/>
                                            <rect x="31" y="22" width="4" height="6" fill="#DAA520"/>
                                            
                                            <!-- Right side inner -->
                                            <rect x="31" y="28" width="4" height="6" fill="#DAA520"/>
                                            <rect x="30" y="32" width="4" height="3" fill="#DAA520"/>
                                            <rect x="26" y="34" width="6" height="2" fill="#DAA520"/>
                                            <rect x="22" y="35" width="6" height="2" fill="#DAA520"/>
                                            <rect x="18" y="34" width="6" height="2" fill="#DAA520"/>
                                            <rect x="16" y="32" width="4" height="3" fill="#DAA520"/>
                                            <rect x="15" y="28" width="4" height="6" fill="#DAA520"/>
                                            
                                            <!-- Center -->
                                            <rect x="21" y="23" width="8" height="4" fill="currentColor"/>
                                            <rect x="22" y="27" width="6" height="2" fill="currentColor"/>
                                            
                                            <!-- Woven petal details -->
                                            <rect x="23" y="10" width="4" height="4" fill="#8B4513"/>
                                            <rect x="38" y="23" width="4" height="4" fill="#8B4513"/>
                                            <rect x="23" y="36" width="4" height="4" fill="#8B4513"/>
                                            <rect x="8" y="23" width="4" height="4" fill="#8B4513"/>
                                            
                                            <rect x="30" y="15" width="4" height="4" fill="#8B4513"/>
                                            <rect x="35" y="30" width="4" height="4" fill="#8B4513"/>
                                            <rect x="30" y="31" width="4" height="4" fill="#8B4513"/>
                                            <rect x="15" y="30" width="4" height="4" fill="#8B4513"/>
                                            <rect x="15" y="15" width="4" height="4" fill="#8B4513"/>
                                        </pattern>
                                    </defs>
                                    <rect width="100" height="100" fill="url(#bangaPattern-preview)" color="#B22222"/>
                                </svg>
                                <div class="absolute inset-0 bg-gradient-to-t from-purple-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                            <h4 class="text-sm font-bold text-gray-900 text-center mb-1 group-hover:text-purple-600 transition-colors">Banga</h4>
                            <p class="text-xs text-gray-600 text-center">Floral Circle</p>
                            <div class="mt-2 flex justify-center space-x-1">
                                <span class="w-2 h-2 bg-purple-400 rounded-full group-hover:animate-pulse"></span>
                                <span class="w-2 h-2 bg-blue-400 rounded-full group-hover:animate-pulse" style="animation-delay: 0.2s"></span>
                                <span class="w-2 h-2 bg-purple-400 rounded-full group-hover:animate-pulse" style="animation-delay: 0.4s"></span>
                            </div>
                        </div>

                        <!-- Kabkab Pattern -->
                        <div class="pattern-card group relative bg-white border-2 border-gray-200 rounded-xl p-5 cursor-pointer hover:border-purple-500 hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-2" onclick="selectPattern('kabkab')" data-pattern="kabkab">
                            <div class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <span class="bg-gradient-to-r from-purple-600 to-blue-600 text-white text-xs px-2 py-1 rounded-full font-semibold">Click to Add</span>
                            </div>
                            <div class="relative overflow-hidden rounded-lg mb-3">
                                <svg id="preview-kabkab" class="w-full h-24 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 transition-all duration-300 group-hover:from-purple-50 group-hover:to-blue-50" viewBox="0 0 100 100">
                                    <!-- Woven Kabkab Star Pattern with Pixel Grid Effect -->
                                    <defs>
                                        <pattern id="kabkabPattern-preview" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                                            <!-- Thick border grid -->
                                            <rect width="50" height="50" fill="none" stroke="#333333" stroke-width="2"/>
                                            
                                            <!-- Pixelated 8-pointed star - woven effect -->
                                            <!-- Top point -->
                                            <rect x="23" y="5" width="4" height="4" fill="currentColor"/>
                                            <rect x="21" y="9" width="8" height="4" fill="currentColor"/>
                                            <rect x="19" y="13" width="12" height="4" fill="currentColor"/>
                                            
                                            <!-- Top-right -->
                                            <rect x="31" y="15" width="4" height="4" fill="currentColor"/>
                                            <rect x="33" y="19" width="4" height="8" fill="currentColor"/>
                                            <rect x="33" y="27" width="4" height="12" fill="currentColor"/>
                                            
                                            <!-- Right -->
                                            <rect x="37" y="21" width="4" height="4" fill="currentColor"/>
                                            <rect x="41" y="19" width="4" height="8" fill="currentColor"/>
                                            <rect x="41" y="27" width="4" height="12" fill="currentColor"/>
                                            
                                            <!-- Bottom-right -->
                                            <rect x="37" y="37" width="4" height="4" fill="currentColor"/>
                                            <rect x="33" y="33" width="4" height="8" fill="currentColor"/>
                                            <rect x="33" y="41" width="4" height="4" fill="currentColor"/>
                                            
                                            <!-- Bottom -->
                                            <rect x="23" y="41" width="4" height="4" fill="currentColor"/>
                                            <rect x="21" y="37" width="8" height="4" fill="currentColor"/>
                                            <rect x="19" y="33" width="12" height="4" fill="currentColor"/>
                                            
                                            <!-- Bottom-left -->
                                            <rect x="15" y="37" width="4" height="4" fill="currentColor"/>
                                            <rect x="13" y="33" width="4" height="8" fill="currentColor"/>
                                            <rect x="13" y="41" width="4" height="4" fill="currentColor"/>
                                            
                                            <!-- Left -->
                                            <rect x="9" y="21" width="4" height="4" fill="currentColor"/>
                                            <rect x="5" y="19" width="4" height="8" fill="currentColor"/>
                                            <rect x="5" y="27" width="4" height="12" fill="currentColor"/>
                                            
                                            <!-- Top-left -->
                                            <rect x="15" y="15" width="4" height="4" fill="currentColor"/>
                                            <rect x="13" y="19" width="4" height="8" fill="currentColor"/>
                                            <rect x="13" y="27" width="4" height="12" fill="currentColor"/>
                                            
                                            <!-- Center hexagon - accent -->
                                            <rect x="21" y="21" width="8" height="4" fill="#DAA520"/>
                                            <rect x="19" y="25" width="12" height="4" fill="#DAA520"/>
                                            <rect x="21" y="29" width="8" height="4" fill="#DAA520"/>
                                            
                                            <!-- Center -->
                                            <rect x="23" y="25" width="4" height="4" fill="currentColor"/>
                                            
                                            <!-- Woven corner dots -->
                                            <rect x="23" y="13" width="4" height="4" fill="#8B4513"/>
                                            <rect x="37" y="25" width="4" height="4" fill="#8B4513"/>
                                            <rect x="23" y="37" width="4" height="4" fill="#8B4513"/>
                                            <rect x="9" y="25" width="4" height="4" fill="#8B4513"/>
                                            
                                            <rect x="31" y="31" width="4" height="4" fill="#8B4513"/>
                                            <rect x="31" y="19" width="4" height="4" fill="#8B4513"/>
                                            <rect x="15" y="19" width="4" height="4" fill="#8B4513"/>
                                            <rect x="15" y="31" width="4" height="4" fill="#8B4513"/>
                                        </pattern>
                                    </defs>
                                    <rect width="100" height="100" fill="url(#kabkabPattern-preview)" color="#B22222"/>
                                </svg>
                                <div class="absolute inset-0 bg-gradient-to-t from-purple-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                            <h4 class="text-sm font-bold text-gray-900 text-center mb-1 group-hover:text-purple-600 transition-colors">Kabkab</h4>
                            <p class="text-xs text-gray-600 text-center">Star Pattern</p>
                            <div class="mt-2 flex justify-center space-x-1">
                                <span class="w-2 h-2 bg-purple-400 rounded-full group-hover:animate-pulse"></span>
                                <span class="w-2 h-2 bg-blue-400 rounded-full group-hover:animate-pulse" style="animation-delay: 0.2s"></span>
                                <span class="w-2 h-2 bg-purple-400 rounded-full group-hover:animate-pulse" style="animation-delay: 0.4s"></span>
                            </div>
                        </div>

                        <!-- Sinag Pattern -->
                        <div class="pattern-card group relative bg-white border-2 border-gray-200 rounded-xl p-5 cursor-pointer hover:border-purple-500 hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-2" onclick="selectPattern('sinag')" data-pattern="sinag">
                            <div class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <span class="bg-gradient-to-r from-purple-600 to-blue-600 text-white text-xs px-2 py-1 rounded-full font-semibold">Click to Add</span>
                            </div>
                            <div class="relative overflow-hidden rounded-lg mb-3">
                                <svg id="preview-sinag" class="w-full h-24 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 transition-all duration-300 group-hover:from-purple-50 group-hover:to-blue-50" viewBox="0 0 100 100">
                                    <!-- Woven Sinag Sun Pattern with Pixel Grid Effect -->
                                    <defs>
                                        <pattern id="sinagPattern-preview" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                                            <!-- Thick border grid -->
                                            <rect width="50" height="50" fill="none" stroke="#333333" stroke-width="2"/>
                                            
                                            <!-- Pixelated sun pattern - woven effect -->
                                            <!-- Center sun -->
                                            <rect x="21" y="21" width="8" height="8" fill="currentColor"/>
                                            <rect x="23" y="23" width="4" height="4" fill="#DAA520"/>
                                            
                                            <!-- Main rays (cardinal directions) -->
                                            <!-- Top -->
                                            <rect x="23" y="5" width="4" height="8" fill="currentColor"/>
                                            <rect x="23" y="13" width="4" height="4" fill="currentColor"/>
                                            
                                            <!-- Bottom -->
                                            <rect x="23" y="37" width="4" height="8" fill="currentColor"/>
                                            <rect x="23" y="33" width="4" height="4" fill="currentColor"/>
                                            
                                            <!-- Left -->
                                            <rect x="5" y="23" width="8" height="4" fill="currentColor"/>
                                            <rect x="13" y="23" width="4" height="4" fill="currentColor"/>
                                            
                                            <!-- Right -->
                                            <rect x="37" y="23" width="8" height="4" fill="currentColor"/>
                                            <rect x="33" y="23" width="4" height="4" fill="currentColor"/>
                                            
                                            <!-- Diagonal rays - woven texture -->
                                            <!-- Top-right -->
                                            <rect x="31" y="11" width="4" height="4" fill="#DAA520"/>
                                            <rect x="35" y="15" width="4" height="4" fill="#DAA520"/>
                                            <rect x="37" y="17" width="4" height="4" fill="#DAA520"/>
                                            
                                            <!-- Bottom-right -->
                                            <rect x="35" y="31" width="4" height="4" fill="#DAA520"/>
                                            <rect x="37" y="33" width="4" height="4" fill="#DAA520"/>
                                            <rect x="39" y="35" width="4" height="4" fill="#DAA520"/>
                                            
                                            <!-- Bottom-left -->
                                            <rect x="15" y="31" width="4" height="4" fill="#DAA520"/>
                                            <rect x="11" y="33" width="4" height="4" fill="#DAA520"/>
                                            <rect x="9" y="35" width="4" height="4" fill="#DAA520"/>
                                            
                                            <!-- Top-left -->
                                            <rect x="11" y="11" width="4" height="4" fill="#DAA520"/>
                                            <rect x="9" y="15" width="4" height="4" fill="#DAA520"/>
                                            <rect x="7" y="17" width="4" height="4" fill="#DAA520"/>
                                            
                                            <!-- Additional diagonal details -->
                                            <rect x="27" y="13" width="4" height="4" fill="#8B4513"/>
                                            <rect x="33" y="19" width="4" height="4" fill="#8B4513"/>
                                            <rect x="33" y="27" width="4" height="4" fill="#8B4513"/>
                                            <rect x="27" y="33" width="4" height="4" fill="#8B4513"/>
                                            <rect x="19" y="33" width="4" height="4" fill="#8B4513"/>
                                            <rect x="13" y="27" width="4" height="4" fill="#8B4513"/>
                                            <rect x="13" y="19" width="4" height="4" fill="#8B4513"/>
                                            <rect x="19" y="13" width="4" height="4" fill="#8B4513"/>
                                        </pattern>
                                    </defs>
                                    <rect width="100" height="100" fill="url(#sinagPattern-preview)" color="#B22222"/>
                                </svg>
                                <div class="absolute inset-0 bg-gradient-to-t from-purple-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                            <h4 class="text-sm font-bold text-gray-900 text-center mb-1 group-hover:text-purple-600 transition-colors">Sinag</h4>
                            <p class="text-xs text-gray-600 text-center">Sun Ray</p>
                            <div class="mt-2 flex justify-center space-x-1">
                                <span class="w-2 h-2 bg-purple-400 rounded-full group-hover:animate-pulse"></span>
                                <span class="w-2 h-2 bg-blue-400 rounded-full group-hover:animate-pulse" style="animation-delay: 0.2s"></span>
                                <span class="w-2 h-2 bg-purple-400 rounded-full group-hover:animate-pulse" style="animation-delay: 0.4s"></span>
                            </div>
                        </div>

                        <!-- Alon Pattern -->
                        <div class="pattern-card group relative bg-white border-2 border-gray-200 rounded-xl p-5 cursor-pointer hover:border-purple-500 hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-2" onclick="selectPattern('alon')" data-pattern="alon">
                            <div class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <span class="bg-gradient-to-r from-purple-600 to-blue-600 text-white text-xs px-2 py-1 rounded-full font-semibold">Click to Add</span>
                            </div>
                            <div class="relative overflow-hidden rounded-lg mb-3">
                                <svg id="preview-alon" class="w-full h-24 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 transition-all duration-300 group-hover:from-purple-50 group-hover:to-blue-50" viewBox="0 0 100 100">
                                    <!-- Woven Alon Wave Pattern with Pixel Grid Effect -->
                                    <defs>
                                        <pattern id="alonPattern-preview" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                                            <!-- Thick border grid -->
                                            <rect width="50" height="50" fill="none" stroke="#333333" stroke-width="2"/>
                                            
                                            <!-- Pixelated wave pattern - woven effect -->
                                            <!-- Layer 1 zigzag - main wave -->
                                            <rect x="2" y="23" width="4" height="4" fill="currentColor"/>
                                            <rect x="6" y="19" width="4" height="4" fill="currentColor"/>
                                            <rect x="10" y="15" width="4" height="4" fill="currentColor"/>
                                            <rect x="14" y="11" width="4" height="4" fill="currentColor"/>
                                            <rect x="18" y="7" width="4" height="4" fill="currentColor"/>
                                            <rect x="22" y="11" width="4" height="4" fill="currentColor"/>
                                            <rect x="26" y="15" width="4" height="4" fill="currentColor"/>
                                            <rect x="30" y="19" width="4" height="4" fill="currentColor"/>
                                            <rect x="34" y="23" width="4" height="4" fill="currentColor"/>
                                            <rect x="38" y="27" width="4" height="4" fill="currentColor"/>
                                            <rect x="42" y="31" width="4" height="4" fill="currentColor"/>
                                            <rect x="46" y="35" width="4" height="4" fill="currentColor"/>
                                            
                                            <!-- Continue wave down -->
                                            <rect x="42" y="39" width="4" height="4" fill="currentColor"/>
                                            <rect x="38" y="43" width="4" height="4" fill="currentColor"/>
                                            <rect x="34" y="47" width="4" height="4" fill="currentColor"/>
                                            <rect x="30" y="43" width="4" height="4" fill="currentColor"/>
                                            <rect x="26" y="39" width="4" height="4" fill="currentColor"/>
                                            <rect x="22" y="35" width="4" height="4" fill="currentColor"/>
                                            <rect x="18" y="31" width="4" height="4" fill="currentColor"/>
                                            <rect x="14" y="27" width="4" height="4" fill="currentColor"/>
                                            <rect x="10" y="23" width="4" height="4" fill="currentColor"/>
                                            <rect x="6" y="27" width="4" height="4" fill="currentColor"/>
                                            <rect x="2" y="31" width="4" height="4" fill="currentColor"/>
                                            
                                            <!-- Layer 2 zigzag - accent wave (offset) -->
                                            <rect x="10" y="19" width="4" height="4" fill="#DAA520"/>
                                            <rect x="14" y="15" width="4" height="4" fill="#DAA520"/>
                                            <rect x="18" y="11" width="4" height="4" fill="#DAA520"/>
                                            <rect x="22" y="15" width="4" height="4" fill="#DAA520"/>
                                            <rect x="26" y="19" width="4" height="4" fill="#DAA520"/>
                                            <rect x="30" y="23" width="4" height="4" fill="#DAA520"/>
                                            <rect x="34" y="27" width="4" height="4" fill="#DAA520"/>
                                            <rect x="38" y="31" width="4" height="4" fill="#DAA520"/>
                                            <rect x="42" y="35" width="4" height="4" fill="#DAA520"/>
                                            <rect x="46" y="39" width="4" height="4" fill="#DAA520"/>
                                            
                                            <!-- Continue accent wave -->
                                            <rect x="42" y="43" width="4" height="4" fill="#DAA520"/>
                                            <rect x="38" y="47" width="4" height="4" fill="#DAA520"/>
                                            <rect x="34" y="43" width="4" height="4" fill="#DAA520"/>
                                            <rect x="30" y="39" width="4" height="4" fill="#DAA520"/>
                                            <rect x="26" y="35" width="4" height="4" fill="#DAA520"/>
                                            <rect x="22" y="31" width="4" height="4" fill="#DAA520"/>
                                            <rect x="18" y="27" width="4" height="4" fill="#DAA520"/>
                                            <rect x="14" y="23" width="4" height="4" fill="#DAA520"/>
                                            <rect x="10" y="27" width="4" height="4" fill="#DAA520"/>
                                            <rect x="6" y="31" width="4" height="4" fill="#DAA520"/>
                                            
                                            <!-- Layer 3 zigzag - woven texture (offset) -->
                                            <rect x="6" y="23" width="4" height="4" fill="#8B4513"/>
                                            <rect x="10" y="19" width="4" height="4" fill="#8B4513"/>
                                            <rect x="14" y="15" width="4" height="4" fill="#8B4513"/>
                                            <rect x="18" y="11" width="4" height="4" fill="#8B4513"/>
                                            <rect x="22" y="15" width="4" height="4" fill="#8B4513"/>
                                            <rect x="26" y="19" width="4" height="4" fill="#8B4513"/>
                                            <rect x="30" y="23" width="4" height="4" fill="#8B4513"/>
                                            <rect x="34" y="27" width="4" height="4" fill="#8B4513"/>
                                            <rect x="38" y="31" width="4" height="4" fill="#8B4513"/>
                                            <rect x="42" y="35" width="4" height="4" fill="#8B4513"/>
                                            <rect x="46" y="39" width="4" height="4" fill="#8B4513"/>
                                        </pattern>
                                    </defs>
                                    <rect width="100" height="100" fill="url(#alonPattern-preview)" color="#B22222"/>
                                </svg>
                                <div class="absolute inset-0 bg-gradient-to-t from-purple-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                            <h4 class="text-sm font-bold text-gray-900 text-center mb-1 group-hover:text-purple-600 transition-colors">Alon</h4>
                            <p class="text-xs text-gray-600 text-center">Wave Pattern</p>
                            <div class="mt-2 flex justify-center space-x-1">
                                <span class="w-2 h-2 bg-purple-400 rounded-full group-hover:animate-pulse"></span>
                                <span class="w-2 h-2 bg-blue-400 rounded-full group-hover:animate-pulse" style="animation-delay: 0.2s"></span>
                                <span class="w-2 h-2 bg-purple-400 rounded-full group-hover:animate-pulse" style="animation-delay: 0.4s"></span>
                            </div>
                        </div>

                        <!-- Dalisay Pattern -->
                        <div class="pattern-card group relative bg-white border-2 border-gray-200 rounded-xl p-5 cursor-pointer hover:border-purple-500 hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:-translate-y-2" onclick="selectPattern('dalisay')" data-pattern="dalisay">
                            <div class="absolute -top-2 -right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <span class="bg-gradient-to-r from-purple-600 to-blue-600 text-white text-xs px-2 py-1 rounded-full font-semibold">Click to Add</span>
                            </div>
                            <div class="relative overflow-hidden rounded-lg mb-3">
                                <svg id="preview-dalisay" class="w-full h-24 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 transition-all duration-300 group-hover:from-purple-50 group-hover:to-blue-50" viewBox="0 0 100 100">
                                    <!-- Large Repeating Diamond Motif with Pixel Grid Effect -->
                                    <defs>
                                        <pattern id="dalisayPattern-preview" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse">
                                            <!-- Thick border grid -->
                                            <rect width="60" height="60" fill="none" stroke="#333333" stroke-width="3"/>
                                            
                                            <!-- Large diamond motif - center -->
                                            <!-- Top point -->
                                            <rect x="28" y="5" width="4" height="4" fill="currentColor"/>
                                            <rect x="26" y="9" width="8" height="4" fill="currentColor"/>
                                            <rect x="24" y="13" width="12" height="4" fill="currentColor"/>
                                            <rect x="22" y="17" width="16" height="4" fill="currentColor"/>
                                            <rect x="20" y="21" width="20" height="4" fill="currentColor"/>
                                            <rect x="18" y="25" width="24" height="4" fill="currentColor"/>
                                            
                                            <!-- Middle section -->
                                            <rect x="16" y="29" width="28" height="4" fill="currentColor"/>
                                            <rect x="14" y="33" width="32" height="4" fill="currentColor"/>
                                            <rect x="12" y="37" width="36" height="4" fill="currentColor"/>
                                            <rect x="10" y="41" width="40" height="4" fill="currentColor"/>
                                            <rect x="8" y="45" width="44" height="4" fill="currentColor"/>
                                            <rect x="6" y="49" width="48" height="4" fill="currentColor"/>
                                            
                                            <!-- Bottom half -->
                                            <rect x="8" y="53" width="44" height="4" fill="currentColor"/>
                                            <rect x="10" y="57" width="40" height="4" fill="currentColor"/>
                                            
                                            <!-- Inner diamond - accent pattern -->
                                            <rect x="26" y="21" width="8" height="4" fill="#DAA520"/>
                                            <rect x="24" y="25" width="12" height="4" fill="#DAA520"/>
                                            <rect x="22" y="29" width="16" height="4" fill="#DAA520"/>
                                            <rect x="20" y="33" width="20" height="4" fill="#DAA520"/>
                                            <rect x="18" y="37" width="24" height="4" fill="#DAA520"/>
                                            <rect x="20" y="41" width="20" height="4" fill="#DAA520"/>
                                            <rect x="22" y="45" width="16" height="4" fill="#DAA520"/>
                                            <rect x="24" y="49" width="12" height="4" fill="#DAA520"/>
                                            <rect x="26" y="53" width="8" height="4" fill="#DAA520"/>
                                            
                                            <!-- Center detail -->
                                            <rect x="28" y="33" width="4" height="4" fill="currentColor"/>
                                            
                                            <!-- Corner woven accents -->
                                            <rect x="28" y="13" width="4" height="4" fill="#8B4513"/>
                                            <rect x="45" y="30" width="4" height="4" fill="#8B4513"/>
                                            <rect x="28" y="47" width="4" height="4" fill="#8B4513"/>
                                            <rect x="11" y="30" width="4" height="4" fill="#8B4513"/>
                                            
                                            <!-- Additional woven details -->
                                            <rect x="38" y="20" width="4" height="4" fill="#8B4513"/>
                                            <rect x="18" y="20" width="4" height="4" fill="#8B4513"/>
                                            <rect x="38" y="40" width="4" height="4" fill="#8B4513"/>
                                            <rect x="18" y="40" width="4" height="4" fill="#8B4513"/>
                                            
                                            <!-- Horizontal connecting lines for tile effect -->
                                            <rect x="0" y="29" width="6" height="2" fill="currentColor"/>
                                            <rect x="54" y="29" width="6" height="2" fill="currentColor"/>
                                            <rect x="0" y="33" width="6" height="2" fill="currentColor"/>
                                            <rect x="54" y="33" width="6" height="2" fill="currentColor"/>
                                        </pattern>
                                    </defs>
                                    <rect width="100" height="100" fill="url(#dalisayPattern-preview)" color="#B22222"/>
                                </svg>
                                <div class="absolute inset-0 bg-gradient-to-t from-purple-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                            <h4 class="text-sm font-bold text-gray-900 text-center mb-1 group-hover:text-purple-600 transition-colors">Dalisay</h4>
                            <p class="text-xs text-gray-600 text-center">Large Diamond Motif</p>
                            <div class="mt-2 flex justify-center space-x-1">
                                <span class="w-2 h-2 bg-purple-400 rounded-full group-hover:animate-pulse"></span>
                                <span class="w-2 h-2 bg-blue-400 rounded-full group-hover:animate-pulse" style="animation-delay: 0.2s"></span>
                                <span class="w-2 h-2 bg-purple-400 rounded-full group-hover:animate-pulse" style="animation-delay: 0.4s"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Color Selection -->
                    <div class="mt-10 p-6 bg-gradient-to-r from-purple-50 via-blue-50 to-purple-50 rounded-2xl">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h4 class="text-xl font-bold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                    </svg>
                                    Pattern Color
                                </h4>
                                <p class="text-sm text-gray-600 mt-1">Choose from our palette or create your own</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 mb-1">Current</p>
                                    <div id="currentColorDisplay" class="w-14 h-14 rounded-xl border-3 border-white shadow-lg transition-all duration-300 hover:scale-110" style="background-color: #B22222;"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Color Palette - Traditional Yakan Colors -->
                        <div class="space-y-6 mb-6">
                            <!-- Primary Yakan Colors -->
                            <div>
                                <h5 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Primary Colors
                                </h5>
                                <div class="grid grid-cols-5 sm:grid-cols-5 lg:grid-cols-5 gap-3">
                                    <button onclick="setColor('#B22222')" class="color-btn group relative w-14 h-14 bg-red-700 rounded-xl border-3 border-white shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1">
                                        <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 text-xs bg-gray-800 text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">Crimson</span>
                                    </button>
                                    <button onclick="setColor('#DAA520')" class="color-btn group relative w-14 h-14 bg-yellow-600 rounded-xl border-3 border-white shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1">
                                        <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 text-xs bg-gray-800 text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">Goldenrod</span>
                                    </button>
                                    <button onclick="setColor('#8B4513')" class="color-btn group relative w-14 h-14 bg-amber-700 rounded-xl border-3 border-white shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1">
                                        <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 text-xs bg-gray-800 text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">Saddle</span>
                                    </button>
                                    <button onclick="setColor('#D2691E')" class="color-btn group relative w-14 h-14 bg-orange-600 rounded-xl border-3 border-white shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1">
                                        <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 text-xs bg-gray-800 text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">Chocolate</span>
                                    </button>
                                    <button onclick="setColor('#A0522D')" class="color-btn group relative w-14 h-14 bg-yellow-700 rounded-xl border-3 border-white shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1">
                                        <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 text-xs bg-gray-800 text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">Sienna</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Secondary Yakan Colors -->
                            <div>
                                <h5 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v11a3 3 0 106 0V4a2 2 0 00-2-2H4zm1 14a1 1 0 100-2 1 1 0 000 2zm5-1.757l4.9-4.9a2 2 0 000-2.828L13.485 5.1a2 2 0 00-2.828 0L10 5.757v8.486zM16 18H9.071l6-6H16a2 2 0 012 2v2a2 2 0 01-2 2z" clip-rule="evenodd"/>
                                    </svg>
                                    Secondary Colors
                                </h5>
                                <div class="grid grid-cols-5 sm:grid-cols-5 lg:grid-cols-5 gap-3">
                                    <button onclick="setColor('#FF8C00')" class="color-btn group relative w-14 h-14 bg-orange-500 rounded-xl border-3 border-white shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1">
                                        <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 text-xs bg-gray-800 text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">Dark Orange</span>
                                    </button>
                                    <button onclick="setColor('#CD853F')" class="color-btn group relative w-14 h-14 bg-yellow-600 rounded-xl border-3 border-white shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1">
                                        <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 text-xs bg-gray-800 text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">Peru</span>
                                    </button>
                                    <button onclick="setColor('#DEB887')" class="color-btn group relative w-14 h-14 bg-orange-300 rounded-xl border-3 border-white shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1">
                                        <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 text-xs bg-gray-800 text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">Burlywood</span>
                                    </button>
                                    <button onclick="setColor('#F5DEB3')" class="color-btn group relative w-14 h-14 bg-orange-200 rounded-xl border-3 border-gray-300 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1">
                                        <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 text-xs bg-gray-800 text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">Wheat</span>
                                    </button>
                                    <button onclick="setColor('#8B7355')" class="color-btn group relative w-14 h-14 bg-yellow-700 rounded-xl border-3 border-white shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-110 hover:-translate-y-1">
                                        <span class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 text-xs bg-gray-800 text-white px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">Burlwood</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Custom Color Picker -->
                        <div class="flex items-center space-x-4 p-4 bg-white rounded-xl shadow-inner">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Custom Color</label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" id="customColor" class="w-20 h-12 rounded-lg cursor-pointer border-2 border-gray-300 hover:border-purple-500 transition-all" onchange="setColor(this.value)">
                                    <input type="text" id="colorHex" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="#B22222" value="#B22222">
                                    <button onclick="applyCustomColor()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-all font-semibold">
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Pattern Display -->
                    <div id="selectedPatternInfo" class="mt-8 bg-gradient-to-r from-purple-600 to-blue-600 p-1 rounded-2xl shadow-2xl hidden">
                        <div class="bg-white rounded-xl p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="relative">
                                        <div id="selectedPatternPreview" class="w-16 h-16 bg-white rounded-xl border-3 border-purple-300 shadow-lg transition-all duration-300 hover:scale-110"></div>
                                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white animate-pulse"></div>
                                    </div>
                                    <div>
                                        <p class="text-lg font-bold text-gray-900" id="selectedPatternName">None</p>
                                        <p class="text-sm text-gray-600">Ready to add to preview</p>
                                        <div class="flex items-center space-x-2 mt-2">
                                            <div class="w-3 h-3 bg-green-500 rounded-full animate-bounce"></div>
                                            <span class="text-xs text-green-600 font-semibold">Selected</span>
                                        </div>
                                    </div>
                                </div>
                                <button onclick="clearSelectedPattern()" class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-all duration-300 transform hover:scale-110">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Preview Area -->
        <div class="hidden lg:block lg:w-1/2 bg-gray-50 p-4 lg:p-8">
            <div class="bg-white rounded-2xl shadow-2xl h-full overflow-hidden">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-blue-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Live Preview
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Your pattern design appears here</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-xs text-green-600 font-semibold">Live</span>
                        </div>
                    </div>
                </div>
                
                <div class="p-6 bg-gradient-to-br from-gray-50 to-gray-100">
                    <div class="flex justify-center mb-6">
                        <div class="relative">
                            <canvas id="livePreview" class="border-4 border-white rounded-2xl bg-white shadow-2xl transition-all duration-300 hover:shadow-3xl" width="400" height="300"></canvas>
                            <div class="absolute -top-2 -right-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white text-xs px-3 py-1 rounded-full font-semibold shadow-lg">
                                400x300
                            </div>
                            <div class="absolute -bottom-2 -left-2 bg-gradient-to-r from-green-600 to-blue-600 text-white text-xs px-3 py-1 rounded-full font-semibold shadow-lg">
                                Preview Canvas
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pattern Count -->
                    <div class="bg-white rounded-xl p-4 mb-6 shadow-inner">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg flex items-center justify-center text-white font-bold">
                                    <span id="patternCount">0</span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Patterns Added</p>
                                    <p class="text-xs text-gray-600">Click patterns to add them instantly</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">Canvas Size</p>
                                <p class="text-sm font-semibold text-gray-900">400x300px</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pattern Controls -->
                    <div class="grid grid-cols-2 gap-4">
                        <button onclick="showcasePatterns()" class="group relative px-6 py-4 bg-gradient-to-r from-purple-500 to-blue-500 text-white rounded-xl hover:from-purple-600 hover:to-blue-600 transition-all duration-300 font-semibold shadow-lg hover:shadow-2xl transform hover:scale-105 hover:-translate-y-1">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 4h16a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V5a1 1 0 011-1z"/>
                                </svg>
                                Showcase All
                            </span>
                            <div class="absolute inset-0 rounded-xl bg-white opacity-0 group-hover:opacity-20 transition-opacity"></div>
                        </button>
                        <button onclick="clearPreview()" class="group relative px-6 py-4 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl hover:from-gray-600 hover:to-gray-700 transition-all duration-300 font-semibold shadow-lg hover:shadow-2xl transform hover:scale-105 hover:-translate-y-1">
                            <span class="flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Clear Preview
                            </span>
                            <div class="absolute inset-0 rounded-xl bg-white opacity-0 group-hover:opacity-20 transition-opacity"></div>
                        </button>
                    </div>
                    
                    <!-- Additional Tools -->
                    <div class="mt-6 grid grid-cols-3 gap-2">
                        <button onclick="downloadPreview()" class="p-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-300 transform hover:scale-105">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span class="text-xs mt-1 block">Download</span>
                        </button>
                        <button onclick="randomizePattern()" class="p-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-300 transform hover:scale-105">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span class="text-xs mt-1 block">Random</span>
                        </button>
                        <button onclick="toggleGrid()" class="p-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all duration-300 transform hover:scale-105">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            <span class="text-xs mt-1 block">Grid</span>
                        </button>
                        <button onclick="testPattern()" class="p-3 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-all duration-300 transform hover:scale-105">
                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <span class="text-xs mt-1 block">Test</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Buttons -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ route('custom_orders.create.step1') }}" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-all font-semibold shadow-lg">
                ← Previous
            </a>
            
            <div class="flex items-center space-x-4">
                <button onclick="saveAsDraft()" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all font-semibold">
                    Save as Draft
                </button>
                <button onclick="proceedToNext()" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg hover:from-purple-700 hover:to-blue-700 transition-all font-semibold shadow-lg">
                    Next →
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Detect which flow we're in
const isProductFlow = {{ isset($isProductFlow) && $isProductFlow ? 'true' : 'false' }};
const isAdminFlow = {{ isset($isAdminFlow) && $isAdminFlow ? 'true' : 'false' }};
const isFabricFlow = {{ isset($isFabricFlow) && $isFabricFlow ? 'true' : 'false' }};

function proceedToNext() {
    // Check if we have patterns selected
    if (addedPatterns.length === 0) {
        showNotification('Please add at least one pattern before proceeding', 'warning');
        return;
    }
    
    // Show loading state
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    loadingDiv.innerHTML = `
        <div class="bg-white rounded-lg p-6 flex items-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mr-3"></div>
            <span class="text-gray-700">Saving your design...</span>
        </div>
    `;
    document.body.appendChild(loadingDiv);

    // Prepare pattern data
    const patternData = {
        patterns: addedPatterns,
        selectedPattern: selectedPattern,
        selectedColor: selectedColor
    };

    if (isAdminFlow) {
        // Admin flow - submit to appropriate admin endpoint
        const form = document.createElement('form');
        form.method = 'POST';
        
        if (isProductFlow) {
            // Admin product flow
            form.action = '{{ route("admin_custom_orders.store.product.customization") }}';
        } else {
            // Admin fabric flow
            form.action = '{{ route("admin_custom_orders.store.pattern") }}';
        }
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add pattern data
        const patternInput = document.createElement('input');
        patternInput.type = 'hidden';
        patternInput.name = 'pattern';
        patternInput.value = selectedPattern;
        form.appendChild(patternInput);
        
        // Add colors data
        const colorsInput = document.createElement('input');
        colorsInput.type = 'hidden';
        colorsInput.name = 'colors';
        colorsInput.value = JSON.stringify([selectedColor]);
        form.appendChild(colorsInput);
        
        // Add pattern data
        const patternDataInput = document.createElement('input');
        patternDataInput.type = 'hidden';
        patternDataInput.name = 'pattern_data';
        patternDataInput.value = JSON.stringify(patternData);
        form.appendChild(patternDataInput);
        
        // Add notes field for admin
        const notesInput = document.createElement('input');
        notesInput.type = 'hidden';
        notesInput.name = 'notes';
        notesInput.value = '';
        form.appendChild(notesInput);
        
        // Add estimated price field for admin
        const priceInput = document.createElement('input');
        priceInput.type = 'hidden';
        priceInput.name = 'estimated_price';
        priceInput.value = '0';
        form.appendChild(priceInput);
        
        // Add quantity field for admin product flow
        if (isProductFlow) {
            const quantityInput = document.createElement('input');
            quantityInput.type = 'hidden';
            quantityInput.name = 'quantity';
            quantityInput.value = '1';
            form.appendChild(quantityInput);
        }
        
        document.body.appendChild(form);
        form.submit();
    } else {
        // User fabric flow - POST design data to pattern endpoint
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("custom_orders.store.pattern") }}';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        // Add pattern data
        const patternInput = document.createElement('input');
        patternInput.type = 'hidden';
        patternInput.name = 'pattern';
        patternInput.value = selectedPattern || 'custom';
        form.appendChild(patternInput);
        
        // Add colors data
        const colorsInput = document.createElement('input');
        colorsInput.type = 'hidden';
        colorsInput.name = 'colors';
        colorsInput.value = JSON.stringify([selectedColor]);
        form.appendChild(colorsInput);
        
        // Add pattern data
        const patternDataInput = document.createElement('input');
        patternDataInput.type = 'hidden';
        patternDataInput.name = 'pattern_data';
        patternDataInput.value = JSON.stringify(patternData);
        form.appendChild(patternDataInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>


<script>
// Enhanced Pattern Designer for Step 2
let selectedPattern = null;
let selectedColor = '#B22222'; // Traditional Yakan crimson as default
let livePreviewCanvas;
let livePreviewCtx;
let patternCount = 0;
let gridEnabled = false;
let animationFrameId = null;

// Pattern definitions with enhanced properties
const patterns = {
    'sussuh': {
        name: 'Sussuh',
        type: 'polygon',
        points: [[0, -20], [10, -10], [20, 0], [10, 10], [0, 20], [-10, 10], [-20, 0], [-10, -10]],
        defaultColor: '#8B4513',
        description: 'Traditional Diamond Pattern',
        complexity: 'medium'
    },
    'banga': {
        name: 'Banga',
        type: 'circle',
        radius: 15,
        petals: 6,
        defaultColor: '#D2691E',
        description: 'Floral Circle Pattern',
        complexity: 'easy'
    },
    'kabkab': {
        name: 'Kabkab',
        type: 'star',
        points: 5,
        outerRadius: 20,
        innerRadius: 10,
        defaultColor: '#A0522D',
        description: 'Star Pattern',
        complexity: 'medium'
    },
    'sinag': {
        name: 'Sinag',
        type: 'sun',
        radius: 15,
        rays: 8,
        defaultColor: '#FFD700',
        description: 'Sun Ray Pattern',
        complexity: 'easy'
    },
    'alon': {
        name: 'Alon',
        type: 'wave',
        width: 40,
        height: 10,
        defaultColor: '#4682B4',
        description: 'Wave Pattern',
        complexity: 'easy'
    },
    'dalisay': {
        name: 'Dalisay',
        type: 'flower',
        petals: 8,
        radius: 12,
        defaultColor: '#FF69B4',
        description: 'Flower Pattern',
        complexity: 'medium'
    }
};

// Initialize the enhanced designer
document.addEventListener('DOMContentLoaded', function() {
    initializePatternPreviews();
    initializeLivePreview();
    initializeColorPicker();
    startAnimations();
    const serverSelectedPatternName = {!! isset($selectedPattern) ? json_encode(strtolower($selectedPattern->name)) : 'null' !!};
    const serverSelectedPatternData = {!! isset($selectedPattern) ? json_encode($selectedPattern->pattern_data ?? []) : 'null' !!};
    const serverBaseColor = {!! isset($selectedPattern) && $selectedPattern->base_color ? json_encode($selectedPattern->base_color) : 'null' !!};
    const serverSelectedPatternImage = {!! isset($selectedPattern) && ($img = $selectedPattern->media->first()) ? json_encode(asset('storage/' . $img->path)) : 'null' !!};
    const serverSelectedPatternSvgDataUri = {!! isset($selectedPattern) && is_array($selectedPattern->pattern_data) && isset($selectedPattern->pattern_data['svg']) ? json_encode('data:image/svg+xml;base64,' . base64_encode($selectedPattern->pattern_data['svg'])) : 'null' !!};
    function mapPatternNameToKey(name, data) {
        if (!name && data && data.type) name = String(data.type).toLowerCase();
        if (!name) return null;
        const n = name.toLowerCase();
        if (n.includes('sussuh')) return 'sussuh';
        if (n.includes('banga')) return 'banga';
        if (n.includes('kabkab')) return 'kabkab';
        if (n.includes('sinag')) return 'sinag';
        if (n.includes('alon') || n.includes('wave')) return 'alon';
        if (n.includes('dalisay') || n.includes('diamond')) return 'dalisay';
        return data && data.type && patterns[data.type] ? data.type : null;
    }
    const mapped = mapPatternNameToKey(serverSelectedPatternName, serverSelectedPatternData);
    if (serverBaseColor) setColor(serverBaseColor);
    if (mapped && patterns[mapped]) {
        selectPattern(mapped);
    } else if (serverSelectedPatternImage) {
        addImagePatternToCanvas(serverSelectedPatternImage);
    } else if (serverSelectedPatternSvgDataUri) {
        addImagePatternToCanvas(serverSelectedPatternSvgDataUri);
    }
});

function initializePatternPreviews() {
    // Set initial colors for all SVG patterns
    updateAllPatternColors(selectedColor);
}

function updateAllPatternColors(color) {
    // Update all SVG pattern colors
    const patterns = [
        { id: 'sussuh', patternId: 'sussuhPattern-preview' },
        { id: 'banga', patternId: 'bangaPattern-preview' },
        { id: 'kabkab', patternId: 'kabkabPattern-preview' },
        { id: 'sinag', patternId: 'sinagPattern-preview' },
        { id: 'alon', patternId: 'alonPattern-preview' },
        { id: 'dalisay', patternId: 'dalisayPattern-preview' }
    ];
    
    patterns.forEach(({ id }) => {
        const svg = document.getElementById(`preview-${id}`);
        if (svg) {
            // Set the color on the SVG root so currentColor in defs inherits correctly
            svg.style.color = color;
        }
    });
}

function animatePatternPreview(svgElement, patternType, color) {
    // SVG patterns don't need animation like canvas - they're already crisp
    // Just set the color
    const rect = svgElement.querySelector('rect');
    if (rect) {
        rect.setAttribute('color', color);
    }
}

function easeOutElastic(x) {
    const c4 = (2 * Math.PI) / 3;
    return x === 0 ? 0 : x === 1 ? 1 :
        Math.pow(2, -10 * x) * Math.sin((x * 10 - 0.75) * c4) + 1;
}

function initializeLivePreview() {
    console.log('initializeLivePreview called');
    livePreviewCanvas = document.getElementById('livePreview');
    console.log('Canvas element:', livePreviewCanvas);
    
    if (livePreviewCanvas) {
        livePreviewCtx = livePreviewCanvas.getContext('2d');
        console.log('Canvas context:', livePreviewCtx);
        
        // Test draw a simple rectangle to verify canvas works
        livePreviewCtx.fillStyle = '#ff0000';
        livePreviewCtx.fillRect(50, 50, 100, 100);
        console.log('Test rectangle drawn');
        
        // Clear after test
        setTimeout(() => {
            clearCanvas();
            console.log('Canvas cleared after test');
        }, 1000);
        
        clearCanvas();
    } else {
        console.error('Canvas element not found!');
    }
}

function initializeColorPicker() {
    const customColor = document.getElementById('customColor');
    const colorHex = document.getElementById('colorHex');
    
    customColor.addEventListener('input', function(e) {
        colorHex.value = e.target.value;
        setColor(e.target.value);
    });
    
    colorHex.addEventListener('input', function(e) {
        if (/^#[0-9A-F]{6}$/i.test(e.target.value)) {
            customColor.value = e.target.value;
            setColor(e.target.value);
        }
    });
}

function drawPattern(ctx, x, y, patternType, scale = 1) {
    const pattern = patterns[patternType];
    if (!pattern) return;

    // Set stroke style for all patterns
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 2;
    ctx.fillStyle = selectedColor;

    // Simple shape drawing that works reliably
    switch (patternType) {
        case 'sussuh':
            // Diamond shape
            ctx.beginPath();
            ctx.moveTo(x, y - 20 * scale);
            ctx.lineTo(x + 15 * scale, y);
            ctx.lineTo(x, y + 20 * scale);
            ctx.lineTo(x - 15 * scale, y);
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
            break;
        case 'banga':
            // Circle with petals
            ctx.beginPath();
            ctx.arc(x, y, 15 * scale, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            // Add simple petals
            for (let i = 0; i < 6; i++) {
                const angle = (i * 60) * Math.PI / 180;
                const petalX = x + Math.cos(angle) * 12 * scale;
                const petalY = y + Math.sin(angle) * 12 * scale;
                ctx.beginPath();
                ctx.arc(petalX, petalY, 5 * scale, 0, Math.PI * 2);
                ctx.fill();
                ctx.stroke();
            }
            break;
        case 'kabkab':
            // Star shape
            ctx.beginPath();
            for (let i = 0; i < 5; i++) {
                const angle = (i * 72 - 90) * Math.PI / 180;
                const outerX = x + Math.cos(angle) * 20 * scale;
                const outerY = y + Math.sin(angle) * 20 * scale;
                const innerAngle = ((i * 72 + 36) - 90) * Math.PI / 180;
                const innerX = x + Math.cos(innerAngle) * 8 * scale;
                const innerY = y + Math.sin(innerAngle) * 8 * scale;
                
                if (i === 0) {
                    ctx.moveTo(outerX, outerY);
                } else {
                    ctx.lineTo(outerX, outerY);
                }
                ctx.lineTo(innerX, innerY);
            }
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
            break;
        case 'sinag':
            // Sun with rays
            ctx.beginPath();
            ctx.arc(x, y, 15 * scale, 0, Math.PI * 2);
            ctx.fill();
            ctx.stroke();
            // Add rays
            for (let i = 0; i < 8; i++) {
                const angle = (i * 45) * Math.PI / 180;
                ctx.beginPath();
                ctx.moveTo(x + Math.cos(angle) * 18 * scale, y + Math.sin(angle) * 18 * scale);
                ctx.lineTo(x + Math.cos(angle) * 25 * scale, y + Math.sin(angle) * 25 * scale);
                ctx.strokeStyle = selectedColor;
                ctx.lineWidth = 3;
                ctx.stroke();
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 2;
            }
            break;
        case 'alon':
            // Wave pattern
            ctx.beginPath();
            for (let i = 0; i <= 20; i++) {
                const waveX = x - 20 * scale + (40 * scale / 20) * i;
                const waveY = y + Math.sin((i / 20) * Math.PI * 2) * 8 * scale;
                if (i === 0) {
                    ctx.moveTo(waveX, waveY);
                } else {
                    ctx.lineTo(waveX, waveY);
                }
            }
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
            break;
        case 'dalisay':
            // Large diamond with inner detail
            ctx.beginPath();
            ctx.moveTo(x, y - 25 * scale);
            ctx.lineTo(x + 20 * scale, y);
            ctx.lineTo(x, y + 25 * scale);
            ctx.lineTo(x - 20 * scale, y);
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
            // Inner diamond
            ctx.beginPath();
            ctx.moveTo(x, y - 10 * scale);
            ctx.lineTo(x + 8 * scale, y);
            ctx.lineTo(x, y + 10 * scale);
            ctx.lineTo(x - 8 * scale, y);
            ctx.closePath();
            ctx.fillStyle = '#DAA520';
            ctx.fill();
            ctx.stroke();
            ctx.fillStyle = selectedColor;
            break;
        default:
            // Fallback to simple patterns if needed
            switch (pattern.type) {
                case 'polygon':
                    drawPolygon(ctx, x, y, pattern.points, scale);
                    break;
                case 'circle':
                    drawCircle(ctx, x, y, pattern.radius * scale);
                    break;
                case 'star':
                    drawStar(ctx, x, y, pattern.outerRadius * scale, pattern.innerRadius * scale, pattern.points);
                    break;
                case 'sun':
                    drawSun(ctx, x, y, pattern.radius * scale, pattern.rays);
                    break;
                case 'wave':
                    drawWave(ctx, x, y, pattern.width * scale, pattern.height * scale);
                    break;
                case 'flower':
                    drawFlower(ctx, x, y, pattern.radius * scale, pattern.petals);
                    break;
            }
    }
    // Propagate color to existing items of the currently selected type
    // REMOVED: This was causing infinite recursion
    // drawPattern should not call redrawCanvas as it's called BY redrawCanvas
}

function drawPolygon(ctx, x, y, points, scale) {
    ctx.beginPath();
    points.forEach((point, index) => {
        const scaledX = x + point[0] * scale;
        const scaledY = y + point[1] * scale;
        if (index === 0) {
            ctx.moveTo(scaledX, scaledY);
        } else {
            ctx.lineTo(scaledX, scaledY);
        }
    });
    ctx.closePath();
    ctx.fill();
    ctx.stroke();
}

function drawCircle(ctx, x, y, radius) {
    ctx.beginPath();
    ctx.arc(x, y, radius, 0, 2 * Math.PI);
    ctx.fill();
    ctx.stroke();
}

function drawStar(ctx, x, y, outerRadius, innerRadius, points) {
    const angle = Math.PI / points;
    ctx.beginPath();
    
    for (let i = 0; i < 2 * points; i++) {
        const radius = i % 2 === 0 ? outerRadius : innerRadius;
        const starX = x + radius * Math.sin(i * angle);
        const starY = y - radius * Math.cos(i * angle);
        
        if (i === 0) {
            ctx.moveTo(starX, starY);
        } else {
            ctx.lineTo(starX, starY);
        }
    }
    ctx.closePath();
    ctx.fill();
    ctx.stroke();
}

function drawSun(ctx, x, y, radius, rays) {
    // Draw center circle
    ctx.beginPath();
    ctx.arc(x, y, radius, 0, 2 * Math.PI);
    ctx.fill();
    ctx.stroke();

    // Draw rays
    for (let i = 0; i < rays; i++) {
        const angle = (360 / rays) * i * Math.PI / 180;
        
        ctx.save();
        ctx.translate(x, y);
        ctx.rotate(angle);
        
        ctx.beginPath();
        ctx.rect(-1.5, -radius * 0.9, 3, radius * 0.8);
        ctx.fill();
        ctx.stroke();
        
        ctx.restore();
    }
}

function drawWave(ctx, x, y, width, height) {
    ctx.beginPath();
    const steps = 20;
    for (let i = 0; i <= steps; i++) {
        const waveX = x - width/2 + (width / steps) * i;
        const waveY = y + Math.sin((i / steps) * Math.PI * 2) * height / 2;
        
        if (i === 0) {
            ctx.moveTo(waveX, waveY);
        } else {
            ctx.lineTo(waveX, waveY);
        }
    }
    ctx.closePath();
    ctx.fill();
    ctx.stroke();
}

function drawFlower(ctx, x, y, radius, petals) {
    // Draw petals
    for (let i = 0; i < petals; i++) {
        const angle = (360 / petals) * i * Math.PI / 180;
        const petalX = x + radius * 0.5 * Math.cos(angle);
        const petalY = y + radius * 0.5 * Math.sin(angle);
        
        ctx.beginPath();
        ctx.ellipse(petalX, petalY, radius * 0.6, radius * 0.3, angle, 0, 2 * Math.PI);
        ctx.fill();
        ctx.stroke();
    }

    // Draw center
    ctx.beginPath();
    ctx.arc(x, y, radius * 0.3, 0, 2 * Math.PI);
    ctx.fillStyle = '#FFD700';
    ctx.fill();
    ctx.stroke();
    ctx.fillStyle = selectedColor; // Reset to original color
}

// AUTHENTIC YAKAN PATTERN DRAWING FUNCTIONS
function drawSussuhPattern(ctx, x, y, size) {
    // Pixel-grid woven diamond pattern
    const pixelSize = size / 10; // Scale pixels based on pattern size
    
    // Draw thick border grid
    ctx.strokeStyle = '#333333';
    ctx.lineWidth = 2;
    ctx.strokeRect(x - size, y - size, size * 2, size * 2);
    
    // Reset fill style
    ctx.fillStyle = selectedColor;
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 1;
    
    // Pixelated diamond shape - stitched effect
    // Top point
    ctx.fillRect(x - pixelSize, y - size + pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 2, y - size + pixelSize * 3, pixelSize * 2, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y - size + pixelSize * 5, pixelSize * 3, pixelSize);
    
    // Middle section
    ctx.fillRect(x - pixelSize * 4, y - size + pixelSize * 7, pixelSize * 4, pixelSize);
    ctx.fillRect(x - pixelSize * 5, y - size + pixelSize * 9, pixelSize * 5, pixelSize);
    ctx.fillRect(x - pixelSize * 6, y - size + pixelSize * 11, pixelSize * 6, pixelSize);
    ctx.fillRect(x - pixelSize * 7, y - size + pixelSize * 13, pixelSize * 7, pixelSize);
    ctx.fillRect(x - pixelSize * 8, y - size + pixelSize * 15, pixelSize * 8, pixelSize);
    ctx.fillRect(x - pixelSize * 9, y - size + pixelSize * 17, pixelSize * 9, pixelSize);
    
    // Bottom half
    ctx.fillRect(x - pixelSize * 8, y - size + pixelSize * 19, pixelSize * 8, pixelSize);
    ctx.fillRect(x - pixelSize * 7, y - size + pixelSize * 21, pixelSize * 7, pixelSize);
    ctx.fillRect(x - pixelSize * 6, y - size + pixelSize * 23, pixelSize * 6, pixelSize);
    
    // Inner diamond - accent
    ctx.fillStyle = '#DAA520';
    ctx.fillRect(x - pixelSize * 2, y - size + pixelSize * 9, pixelSize * 2, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y - size + pixelSize * 11, pixelSize * 3, pixelSize);
    ctx.fillRect(x - pixelSize * 4, y - size + pixelSize * 13, pixelSize * 4, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y - size + pixelSize * 15, pixelSize * 3, pixelSize);
    ctx.fillRect(x - pixelSize * 2, y - size + pixelSize * 17, pixelSize * 2, pixelSize);
    
    // Center dot
    ctx.fillStyle = selectedColor;
    ctx.fillRect(x - pixelSize, y - pixelSize, pixelSize, pixelSize);
    
    // Corner accents - woven texture
    ctx.fillStyle = '#DAA520';
    ctx.fillRect(x - pixelSize, y - size + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x + size - pixelSize * 2, y - pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize, y + size - pixelSize * 2, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize, y - pixelSize, pixelSize, pixelSize);
}

function drawBangaPattern(ctx, x, y, size) {
    // Pixel-grid woven circular pattern
    const pixelSize = size / 10;
    
    // Draw thick border grid
    ctx.strokeStyle = '#333333';
    ctx.lineWidth = 2;
    ctx.strokeRect(x - size, y - size, size * 2, size * 2);
    
    // Reset fill style
    ctx.fillStyle = selectedColor;
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 1;
    
    // Pixelated circular pattern - woven effect
    // Outer ring
    ctx.fillRect(x - size + pixelSize, y - pixelSize * 3, pixelSize, pixelSize * 2);
    ctx.fillRect(x - size + pixelSize * 1.5, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 3, y - pixelSize * 6, pixelSize * 2, pixelSize);
    ctx.fillRect(x - size + pixelSize * 6, y - pixelSize * 7, pixelSize * 3, pixelSize);
    ctx.fillRect(x - size + pixelSize * 9, y - pixelSize * 7, pixelSize * 4, pixelSize);
    ctx.fillRect(x - size + pixelSize * 13, y - pixelSize * 6, pixelSize * 3, pixelSize);
    ctx.fillRect(x - size + pixelSize * 16, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 17, y - pixelSize * 3, pixelSize, pixelSize * 2);
    
    // Right side
    ctx.fillRect(x + size - pixelSize * 2, y - pixelSize * 3, pixelSize, pixelSize * 2);
    ctx.fillRect(x + size - pixelSize, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x + size - pixelSize * 3, y - pixelSize * 6, pixelSize * 2, pixelSize);
    ctx.fillRect(x + size - pixelSize * 6, y - pixelSize * 7, pixelSize * 3, pixelSize);
    ctx.fillRect(x + size - pixelSize * 10, y - pixelSize * 7, pixelSize * 4, pixelSize);
    ctx.fillRect(x + size - pixelSize * 13, y - pixelSize * 6, pixelSize * 3, pixelSize);
    ctx.fillRect(x + size - pixelSize * 16, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x + size - pixelSize * 17, y - pixelSize * 3, pixelSize, pixelSize * 2);
    
    // Bottom-right
    ctx.fillRect(x + size - pixelSize * 2, y + pixelSize * 3, pixelSize, pixelSize * 2);
    ctx.fillRect(x + size - pixelSize * 3, y + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x + size - pixelSize * 6, y + pixelSize * 6, pixelSize * 2, pixelSize);
    ctx.fillRect(x + size - pixelSize * 10, y + pixelSize * 7, pixelSize * 3, pixelSize);
    ctx.fillRect(x + size - pixelSize * 13, y + pixelSize * 7, pixelSize * 4, pixelSize);
    ctx.fillRect(x + size - pixelSize * 16, y + pixelSize * 6, pixelSize * 3, pixelSize);
    ctx.fillRect(x + size - pixelSize * 17, y + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x + size - pixelSize * 18, y + pixelSize * 3, pixelSize, pixelSize * 2);
    
    // Bottom
    ctx.fillRect(x - size + pixelSize, y + size - pixelSize * 2, pixelSize, pixelSize * 2);
    ctx.fillRect(x - size + pixelSize * 2, y + size - pixelSize * 4, pixelSize * 2, pixelSize);
    ctx.fillRect(x - size + pixelSize * 4, y + size - pixelSize * 6, pixelSize * 3, pixelSize);
    ctx.fillRect(x - size + pixelSize * 7, y + size - pixelSize * 7, pixelSize * 4, pixelSize);
    ctx.fillRect(x - size + pixelSize * 11, y + size - pixelSize * 7, pixelSize * 5, pixelSize);
    ctx.fillRect(x - size + pixelSize * 16, y + size - pixelSize * 6, pixelSize * 4, pixelSize);
    ctx.fillRect(x - size + pixelSize * 20, y + size - pixelSize * 5, pixelSize * 2, pixelSize);
    ctx.fillRect(x - size + pixelSize * 22, y + size - pixelSize * 3, pixelSize * 2, pixelSize);
    
    // Bottom-left
    ctx.fillRect(x - size + pixelSize, y + pixelSize * 3, pixelSize, pixelSize * 2);
    ctx.fillRect(x - size + pixelSize * 2, y + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 4, y + pixelSize * 6, pixelSize * 2, pixelSize);
    ctx.fillRect(x - size + pixelSize * 7, y + pixelSize * 7, pixelSize * 3, pixelSize);
    ctx.fillRect(x - size + pixelSize * 10, y + pixelSize * 7, pixelSize * 4, pixelSize);
    ctx.fillRect(x - size + pixelSize * 13, y + pixelSize * 6, pixelSize * 3, pixelSize);
    ctx.fillRect(x - size + pixelSize * 16, y + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 17, y + pixelSize * 3, pixelSize, pixelSize * 2);
    
    // Left
    ctx.fillRect(x - size + pixelSize, y - pixelSize * 3, pixelSize, pixelSize * 2);
    ctx.fillRect(x - size + pixelSize * 2, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 4, y - pixelSize * 6, pixelSize * 2, pixelSize);
    ctx.fillRect(x - size + pixelSize * 7, y - pixelSize * 7, pixelSize * 3, pixelSize);
    ctx.fillRect(x - size + pixelSize * 10, y - pixelSize * 7, pixelSize * 4, pixelSize);
    ctx.fillRect(x - size + pixelSize * 13, y - pixelSize * 6, pixelSize * 3, pixelSize);
    ctx.fillRect(x - size + pixelSize * 16, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 17, y - pixelSize * 3, pixelSize, pixelSize * 2);
    
    // Top-left
    ctx.fillRect(x - size + pixelSize, y - pixelSize * 3, pixelSize, pixelSize * 2);
    ctx.fillRect(x - size + pixelSize * 2, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 4, y - pixelSize * 6, pixelSize * 2, pixelSize);
    ctx.fillRect(x - size + pixelSize * 7, y - pixelSize * 7, pixelSize * 3, pixelSize);
    ctx.fillRect(x - size + pixelSize * 10, y - pixelSize * 7, pixelSize * 4, pixelSize);
    ctx.fillRect(x - size + pixelSize * 13, y - pixelSize * 6, pixelSize * 3, pixelSize);
    ctx.fillRect(x - size + pixelSize * 16, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 17, y - pixelSize * 3, pixelSize, pixelSize * 2);
    
    // Inner ring - accent
    ctx.fillStyle = '#DAA520';
    ctx.fillRect(x - pixelSize * 3, y - pixelSize * 3, pixelSize, pixelSize * 2);
    ctx.fillRect(x - pixelSize * 2.5, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 1.5, y - pixelSize * 6, pixelSize * 2, pixelSize);
    ctx.fillRect(x, y - pixelSize * 7, pixelSize * 2, pixelSize);
    ctx.fillRect(x + pixelSize * 2, y - pixelSize * 6, pixelSize * 2, pixelSize);
    ctx.fillRect(x + pixelSize * 3, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x + pixelSize * 3.5, y - pixelSize * 3, pixelSize, pixelSize * 2);
    
    // Right side inner
    ctx.fillRect(x + pixelSize * 3.5, y + pixelSize * 3, pixelSize, pixelSize * 2);
    ctx.fillRect(x + pixelSize * 3, y + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x + pixelSize * 2, y + pixelSize * 6, pixelSize * 2, pixelSize);
    ctx.fillRect(x, y + pixelSize * 7, pixelSize * 2, pixelSize);
    ctx.fillRect(x - pixelSize * 2, y + pixelSize * 6, pixelSize * 2, pixelSize);
    ctx.fillRect(x - pixelSize * 2.5, y + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y + pixelSize * 3, pixelSize, pixelSize * 2);
    
    // Center
    ctx.fillStyle = selectedColor;
    ctx.fillRect(x - pixelSize * 2, y - pixelSize, pixelSize * 3, pixelSize);
    ctx.fillRect(x - pixelSize * 1.5, y + pixelSize, pixelSize * 2, pixelSize);
    
    // Woven petal details
    ctx.fillStyle = '#8B4513';
    ctx.fillRect(x - pixelSize, y - size + pixelSize * 2, pixelSize, pixelSize);
    ctx.fillRect(x + size - pixelSize * 2, y - pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize, y + size - pixelSize * 2, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize, y - pixelSize, pixelSize, pixelSize);
}

function drawKabkabPattern(ctx, x, y, size) {
    // Pixel-grid woven 8-pointed star pattern
    const pixelSize = size / 10;
    
    // Draw thick border grid
    ctx.strokeStyle = '#333333';
    ctx.lineWidth = 2;
    ctx.strokeRect(x - size, y - size, size * 2, size * 2);
    
    // Reset fill style
    ctx.fillStyle = selectedColor;
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 1;
    
    // Pixelated 8-pointed star - woven effect
    // Top point
    ctx.fillRect(x - pixelSize, y - size + pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 2, y - size + pixelSize * 3, pixelSize * 2, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y - size + pixelSize * 5, pixelSize * 3, pixelSize);
    
    // Top-right
    ctx.fillRect(x + pixelSize * 3, y - size + pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x + pixelSize * 4, y - size + pixelSize * 9, pixelSize, pixelSize * 2);
    ctx.fillRect(x + pixelSize * 4, y - size + pixelSize * 11, pixelSize, pixelSize * 3);
    
    // Right
    ctx.fillRect(x + size - pixelSize * 3, y - pixelSize * 2, pixelSize, pixelSize);
    ctx.fillRect(x + size - pixelSize, y - pixelSize * 3, pixelSize, pixelSize * 2);
    ctx.fillRect(x + size - pixelSize, y - pixelSize * 5, pixelSize, pixelSize * 3);
    
    // Bottom-right
    ctx.fillRect(x + size - pixelSize * 3, y + pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x + pixelSize * 4, y + pixelSize * 5, pixelSize, pixelSize * 2);
    ctx.fillRect(x + pixelSize * 4, y + pixelSize * 7, pixelSize, pixelSize);
    
    // Bottom
    ctx.fillRect(x - pixelSize, y + size - pixelSize * 2, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 2, y + size - pixelSize * 4, pixelSize * 2, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y + size - pixelSize * 6, pixelSize * 3, pixelSize);
    
    // Bottom-left
    ctx.fillRect(x - pixelSize * 3, y + pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 4, y + pixelSize * 5, pixelSize, pixelSize * 2);
    ctx.fillRect(x - pixelSize * 4, y + pixelSize * 7, pixelSize, pixelSize);
    
    // Left
    ctx.fillRect(x - size + pixelSize, y - pixelSize * 2, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 2, y - pixelSize * 3, pixelSize, pixelSize * 2);
    ctx.fillRect(x - size + pixelSize * 2, y - pixelSize * 5, pixelSize, pixelSize * 3);
    
    // Top-left
    ctx.fillRect(x - pixelSize * 3, y - size + pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 4, y - size + pixelSize * 9, pixelSize, pixelSize * 2);
    ctx.fillRect(x - pixelSize * 4, y - size + pixelSize * 11, pixelSize, pixelSize * 3);
    
    // Center hexagon - accent
    ctx.fillStyle = '#DAA520';
    ctx.fillRect(x - pixelSize * 2, y - pixelSize, pixelSize * 2, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y, pixelSize * 3, pixelSize);
    ctx.fillRect(x - pixelSize * 2, y + pixelSize, pixelSize * 2, pixelSize);
    
    // Center
    ctx.fillStyle = selectedColor;
    ctx.fillRect(x - pixelSize, y - pixelSize, pixelSize, pixelSize);
    
    // Woven corner dots
    ctx.fillStyle = '#8B4513';
    ctx.fillRect(x - pixelSize, y - size + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x + size - pixelSize * 3, y - pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize, y + size - pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize, y - pixelSize, pixelSize, pixelSize);
    
    ctx.fillRect(x + pixelSize * 3, y + pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x + pixelSize * 3, y - pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y - pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y + pixelSize, pixelSize, pixelSize);
}

function drawSinagPattern(ctx, x, y, size) {
    // Pixel-grid woven sun pattern
    const pixelSize = size / 10;
    
    // Draw thick border grid
    ctx.strokeStyle = '#333333';
    ctx.lineWidth = 2;
    ctx.strokeRect(x - size, y - size, size * 2, size * 2);
    
    // Reset fill style
    ctx.fillStyle = selectedColor;
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 1;
    
    // Pixelated sun pattern - woven effect
    // Center sun
    ctx.fillRect(x - pixelSize * 2, y - pixelSize * 2, pixelSize * 3, pixelSize * 3);
    ctx.fillStyle = '#DAA520';
    ctx.fillRect(x - pixelSize, y - pixelSize, pixelSize, pixelSize);
    
    // Main rays (cardinal directions)
    ctx.fillStyle = selectedColor;
    // Top
    ctx.fillRect(x - pixelSize, y - size + pixelSize, pixelSize, pixelSize * 3);
    ctx.fillRect(x - pixelSize, y - size + pixelSize * 4, pixelSize, pixelSize);
    
    // Bottom
    ctx.fillRect(x - pixelSize, y + size - pixelSize * 4, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize, y + size - pixelSize * 3, pixelSize, pixelSize * 3);
    
    // Left
    ctx.fillRect(x - size + pixelSize, y - pixelSize, pixelSize * 3, pixelSize);
    ctx.fillRect(x - size + pixelSize * 4, y - pixelSize, pixelSize, pixelSize);
    
    // Right
    ctx.fillRect(x + size - pixelSize * 4, y - pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x + size - pixelSize * 3, y - pixelSize, pixelSize * 3, pixelSize);
    
    // Diagonal rays - woven texture
    ctx.fillStyle = '#DAA520';
    // Top-right
    ctx.fillRect(x + pixelSize * 3, y - size + pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x + pixelSize * 4, y - size + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x + pixelSize * 4, y - size + pixelSize * 6, pixelSize, pixelSize);
    
    // Bottom-right
    ctx.fillRect(x + pixelSize * 4, y + size - pixelSize * 6, pixelSize, pixelSize);
    ctx.fillRect(x + pixelSize * 4, y + size - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x + pixelSize * 5, y + size - pixelSize * 4, pixelSize, pixelSize);
    
    // Bottom-left
    ctx.fillRect(x - pixelSize * 4, y + size - pixelSize * 6, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 4, y + size - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 5, y + size - pixelSize * 4, pixelSize, pixelSize);
    
    // Top-left
    ctx.fillRect(x - pixelSize * 4, y - size + pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 4, y - size + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 5, y - size + pixelSize * 6, pixelSize, pixelSize);
    
    // Additional diagonal details
    ctx.fillStyle = '#8B4513';
    ctx.fillRect(x + pixelSize * 2, y - size + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x + pixelSize * 4, y - size + pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x + pixelSize * 4, y + size - pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x + pixelSize * 2, y + size - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 2, y + size - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 4, y + size - pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 4, y - size + pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 2, y - size + pixelSize * 5, pixelSize, pixelSize);
}

function drawAlonPattern(ctx, x, y, size) {
    // Pixel-grid woven wave pattern
    const pixelSize = size / 10;
    
    // Draw thick border grid
    ctx.strokeStyle = '#333333';
    ctx.lineWidth = 2;
    ctx.strokeRect(x - size, y - size, size * 2, size * 2);
    
    // Reset fill style
    ctx.fillStyle = selectedColor;
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 1;
    
    // Pixelated wave pattern - woven effect
    // Layer 1 zigzag - main wave
    ctx.fillRect(x - size + pixelSize * 0.5, y - pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 1.5, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 2.5, y - pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 3.5, y - pixelSize * 9, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 4.5, y - pixelSize * 11, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 5.5, y - pixelSize * 9, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 6.5, y - pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 7.5, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 8.5, y - pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 9.5, y - pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 10.5, y + pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 11.5, y + pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 12.5, y + pixelSize * 5, pixelSize, pixelSize);
    
    // Continue wave down
    ctx.fillRect(x - size + pixelSize * 11.5, y + pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 10.5, y + pixelSize * 9, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 9.5, y + pixelSize * 11, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 8.5, y + pixelSize * 9, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 7.5, y + pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 6.5, y + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 5.5, y + pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 4.5, y + pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 3.5, y - pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 2.5, y + pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 1.5, y + pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 0.5, y + pixelSize * 5, pixelSize, pixelSize);
    
    // Layer 2 zigzag - accent wave (offset)
    ctx.fillStyle = '#DAA520';
    ctx.fillRect(x - size + pixelSize * 2.5, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 3.5, y - pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 4.5, y - pixelSize * 9, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 5.5, y - pixelSize * 11, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 6.5, y - pixelSize * 9, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 7.5, y - pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 8.5, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 9.5, y - pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 10.5, y - pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 11.5, y + pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 12.5, y + pixelSize * 3, pixelSize, pixelSize);
    
    // Continue accent wave
    ctx.fillRect(x - size + pixelSize * 11.5, y + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 10.5, y + pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 9.5, y + pixelSize * 9, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 8.5, y + pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 7.5, y + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 6.5, y + pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 5.5, y + pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 4.5, y - pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 3.5, y + pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 2.5, y + pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 1.5, y + pixelSize * 5, pixelSize, pixelSize);
    
    // Layer 3 zigzag - woven texture (offset)
    ctx.fillStyle = '#8B4513';
    ctx.fillRect(x - size + pixelSize * 1.5, y - pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 2.5, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 3.5, y - pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 4.5, y - pixelSize * 9, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 5.5, y - pixelSize * 11, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 6.5, y - pixelSize * 9, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 7.5, y - pixelSize * 7, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 8.5, y - pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 9.5, y - pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 10.5, y - pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 11.5, y + pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 12.5, y + pixelSize * 3, pixelSize, pixelSize);
}

function drawDalisayPattern(ctx, x, y, size) {
    // Pixel-grid large diamond motif pattern
    const pixelSize = size / 12; // Smaller pixels for more detail
    
    // Draw thick border grid
    ctx.strokeStyle = '#333333';
    ctx.lineWidth = 3;
    ctx.strokeRect(x - size, y - size, size * 2, size * 2);
    
    // Reset fill style
    ctx.fillStyle = selectedColor;
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 1;
    
    // Large diamond motif - center
    // Top point
    ctx.fillRect(x - pixelSize, y - size + pixelSize, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 2, y - size + pixelSize * 3, pixelSize * 2, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y - size + pixelSize * 5, pixelSize * 3, pixelSize);
    ctx.fillRect(x - pixelSize * 4, y - size + pixelSize * 7, pixelSize * 4, pixelSize);
    ctx.fillRect(x - pixelSize * 5, y - size + pixelSize * 9, pixelSize * 5, pixelSize);
    ctx.fillRect(x - pixelSize * 6, y - size + pixelSize * 11, pixelSize * 6, pixelSize);
    
    // Middle section
    ctx.fillRect(x - pixelSize * 7, y - size + pixelSize * 13, pixelSize * 7, pixelSize);
    ctx.fillRect(x - pixelSize * 8, y - size + pixelSize * 15, pixelSize * 8, pixelSize);
    ctx.fillRect(x - pixelSize * 9, y - size + pixelSize * 17, pixelSize * 9, pixelSize);
    ctx.fillRect(x - pixelSize * 10, y - size + pixelSize * 19, pixelSize * 10, pixelSize);
    ctx.fillRect(x - pixelSize * 11, y - size + pixelSize * 21, pixelSize * 11, pixelSize);
    ctx.fillRect(x - pixelSize * 12, y - size + pixelSize * 23, pixelSize * 12, pixelSize);
    
    // Bottom half
    ctx.fillRect(x - pixelSize * 11, y - size + pixelSize * 25, pixelSize * 11, pixelSize);
    ctx.fillRect(x - pixelSize * 10, y - size + pixelSize * 27, pixelSize * 10, pixelSize);
    
    // Inner diamond - accent pattern
    ctx.fillStyle = '#DAA520';
    ctx.fillRect(x - pixelSize * 2, y - size + pixelSize * 9, pixelSize * 2, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y - size + pixelSize * 11, pixelSize * 3, pixelSize);
    ctx.fillRect(x - pixelSize * 4, y - size + pixelSize * 13, pixelSize * 4, pixelSize);
    ctx.fillRect(x - pixelSize * 5, y - size + pixelSize * 15, pixelSize * 5, pixelSize);
    ctx.fillRect(x - pixelSize * 6, y - size + pixelSize * 17, pixelSize * 6, pixelSize);
    ctx.fillRect(x - pixelSize * 5, y - size + pixelSize * 19, pixelSize * 5, pixelSize);
    ctx.fillRect(x - pixelSize * 4, y - size + pixelSize * 21, pixelSize * 4, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y - size + pixelSize * 23, pixelSize * 3, pixelSize);
    ctx.fillRect(x - pixelSize * 2, y - size + pixelSize * 25, pixelSize * 2, pixelSize);
    
    // Center detail
    ctx.fillStyle = selectedColor;
    ctx.fillRect(x - pixelSize, y - pixelSize, pixelSize, pixelSize);
    
    // Corner woven accents
    ctx.fillStyle = '#8B4513';
    ctx.fillRect(x - pixelSize, y - size + pixelSize * 5, pixelSize, pixelSize);
    ctx.fillRect(x + size - pixelSize * 3, y - pixelSize * 2, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize, y + size - pixelSize * 3, pixelSize, pixelSize);
    ctx.fillRect(x - size + pixelSize * 2, y - pixelSize * 2, pixelSize, pixelSize);
    
    // Additional woven details
    ctx.fillRect(x + pixelSize * 3, y - size + pixelSize * 8, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y - size + pixelSize * 8, pixelSize, pixelSize);
    ctx.fillRect(x + pixelSize * 3, y + size - pixelSize * 8, pixelSize, pixelSize);
    ctx.fillRect(x - pixelSize * 3, y + size - pixelSize * 8, pixelSize, pixelSize);
    
    // Horizontal connecting lines for tile effect
    ctx.fillStyle = selectedColor;
    ctx.fillRect(x - size, y - pixelSize * 2, pixelSize * 2, pixelSize);
    ctx.fillRect(x + size - pixelSize * 2, y - pixelSize * 2, pixelSize * 2, pixelSize);
    ctx.fillRect(x - size, y + pixelSize, pixelSize * 2, pixelSize);
    ctx.fillRect(x + size - pixelSize * 2, y + pixelSize, pixelSize * 2, pixelSize);
}

// Helper functions for traditional Yakan color schemes
function getContrastColor(baseColor) {
    const contrasts = {
        '#8B0000': '#FFD700',  // Red -> Gold
        '#FFD700': '#000000',  // Gold -> Black
        '#006400': '#FFD700',  // Green -> Gold
        '#000000': '#FFD700',  // Black -> Gold
        '#8B4513': '#FFFFFF',  // Brown -> White
        '#D2691E': '#FFFFFF',  // Orange -> White
        '#A0522D': '#FFD700',  // Sienna -> Gold
        '#FF69B4': '#8B0000',  // Pink -> Red
        '#FFFFFF': '#000000',  // White -> Black
        '#4B0082': '#FFD700',  // Indigo -> Gold
        '#4682B4': '#FFFFFF',  // Steel -> White (legacy)
        '#FF0000': '#FFD700',  // Bright Red -> Gold
        '#00FF00': '#000000',  // Bright Green -> Black
        '#0000FF': '#FFD700',  // Bright Blue -> Gold
        '#00CED1': '#000000'   // Dark Turquoise -> Black
    };
    return contrasts[baseColor] || '#FFFFFF';
}

function getAccentColor() {
    // Traditional Yakan accent colors
    const accentColors = ['#8B0000', '#FFD700', '#006400', '#000000', '#8B4513', '#FFFFFF'];
    return accentColors[Math.floor(Math.random() * accentColors.length)];
}

function testPattern() {
    console.log('testPattern called');
    console.log('Canvas:', livePreviewCanvas);
    console.log('Context:', livePreviewCtx);
    
    if (!livePreviewCanvas || !livePreviewCtx) {
        console.error('Canvas not available for test');
        return;
    }
    
    // Clear and redraw
    clearCanvas();
    
    // Test all patterns with current color
    console.log('Testing all patterns with color:', selectedColor);
    
    const testPatterns = [
        { type: 'sussuh', x: 100, y: 100 },
        { type: 'banga', x: 300, y: 100 },
        { type: 'kabkab', x: 100, y: 200 },
        { type: 'sinag', x: 300, y: 200 }
    ];
    
    testPatterns.forEach((pattern, index) => {
        setTimeout(() => {
            const newPattern = {
                id: patternIdCounter++,
                type: pattern.type,
                x: pattern.x,
                y: pattern.y,
                color: selectedColor,
                scale: 1
            };
            addedPatterns.push(newPattern);
            redrawCanvas();
            
            if (index === testPatterns.length - 1) {
                showNotification('Test patterns drawn!', 'success');
            }
        }, index * 100);
    });
}

function selectPattern(patternType) {
    console.log('selectPattern called with:', patternType);
    selectedPattern = patternType;
    
    // Check if canvas is available
    if (!livePreviewCanvas || !livePreviewCtx) {
        console.error('Canvas or context not available');
        console.log('livePreviewCanvas:', livePreviewCanvas);
        console.log('livePreviewCtx:', livePreviewCtx);
        return;
    }
    
    // Automatically add pattern to preview
    if (livePreviewCanvas && livePreviewCtx) {
        console.log('Adding pattern to preview');
        // Add pattern at random position with animation
        const x = Math.random() * (livePreviewCanvas.width - 100) + 50;
        const y = Math.random() * (livePreviewCanvas.height - 100) + 50;
        
        // Store the pattern
        const newPattern = {
            id: patternIdCounter++,
            type: patternType,
            x: x,
            y: y,
            color: selectedColor,
            scale: 1
        };
        addedPatterns.push(newPattern);
        
        console.log('New pattern added:', newPattern);
        console.log('Current patterns array:', addedPatterns);
        
        // Animate the addition
        animatePatternAddition(newPattern);
        
        // Update pattern count
        patternCount++;
        document.getElementById('patternCount').textContent = patternCount;
        
        // Show subtle notification
        showNotification(`${patterns[patternType].name} pattern added!`, 'success');
    }
    
    // Update UI with animation
    const infoDiv = document.getElementById('selectedPatternInfo');
    const nameSpan = document.getElementById('selectedPatternName');
    const previewDiv = document.getElementById('selectedPatternPreview');
    
    if (infoDiv && nameSpan && previewDiv) {
        infoDiv.classList.remove('hidden');
        infoDiv.style.animation = 'slideInUp 0.3s ease-out';
        
        nameSpan.textContent = patterns[patternType].name;
        
        // Create SVG preview
        const svgString = createPatternSVG(patternType, selectedColor, 64, 64);
        const encodedSvg = encodeURIComponent(svgString);
        previewDiv.style.backgroundImage = `url("data:image/svg+xml,${encodedSvg}")`;
        previewDiv.style.backgroundSize = 'contain';
        previewDiv.style.backgroundRepeat = 'no-repeat';
        previewDiv.style.backgroundPosition = 'center';
    }

    // Highlight selected pattern card with animation
    document.querySelectorAll('.pattern-card').forEach(card => {
        card.classList.remove('border-purple-600', 'bg-purple-50', 'ring-4', 'ring-purple-200');
        card.classList.add('border-gray-200');
    });

    const selectedCard = document.querySelector(`[data-pattern="${patternType}"]`);
    if (selectedCard) {
        selectedCard.classList.remove('border-gray-200');
        selectedCard.classList.add('border-purple-600', 'bg-purple-50', 'ring-4', 'ring-purple-200');
        selectedCard.style.animation = 'pulse 0.5s ease-out';
    }
}

function createPatternSVG(patternType, color, width, height) {
    const svgTemplates = {
        'sussuh': `<svg width="${width}" height="${height}" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" fill="url(#sussuhPattern-${patternType})" color="${color}"/>
            <defs>
                <pattern id="sussuhPattern-${patternType}" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                    <!-- Thick border grid -->
                    <rect width="50" height="50" fill="none" stroke="#333333" stroke-width="2"/>
                    
                    <!-- Pixelated diamond shape - stitched effect -->
                    <!-- Top point -->
                    <rect x="23" y="2" width="4" height="4" fill="currentColor"/>
                    <rect x="21" y="6" width="8" height="4" fill="currentColor"/>
                    <rect x="19" y="10" width="12" height="4" fill="currentColor"/>
                    
                    <!-- Middle section -->
                    <rect x="17" y="14" width="16" height="4" fill="currentColor"/>
                    <rect x="15" y="18" width="20" height="4" fill="currentColor"/>
                    <rect x="13" y="22" width="24" height="4" fill="currentColor"/>
                    <rect x="11" y="26" width="28" height="4" fill="currentColor"/>
                    <rect x="9" y="30" width="32" height="4" fill="currentColor"/>
                    <rect x="7" y="34" width="36" height="4" fill="currentColor"/>
                    
                    <!-- Bottom half -->
                    <rect x="9" y="38" width="32" height="4" fill="currentColor"/>
                    <rect x="11" y="42" width="28" height="4" fill="currentColor"/>
                    <rect x="13" y="46" width="24" height="4" fill="currentColor"/>
                    
                    <!-- Inner diamond - accent -->
                    <rect x="21" y="18" width="8" height="4" fill="#DAA520"/>
                    <rect x="19" y="22" width="12" height="4" fill="#DAA520"/>
                    <rect x="17" y="26" width="16" height="4" fill="#DAA520"/>
                    <rect x="19" y="30" width="12" height="4" fill="#DAA520"/>
                    <rect x="21" y="34" width="8" height="4" fill="#DAA520"/>
                    
                    <!-- Center dot -->
                    <rect x="23" y="26" width="4" height="4" fill="currentColor"/>
                    
                    <!-- Corner accents - woven texture -->
                    <rect x="23" y="10" width="4" height="4" fill="#DAA520"/>
                    <rect x="38" y="25" width="4" height="4" fill="#DAA520"/>
                    <rect x="23" y="40" width="4" height="4" fill="#DAA520"/>
                    <rect x="8" y="25" width="4" height="4" fill="#DAA520"/>
                </pattern>
            </defs>
        </svg>`,
        'banga': `<svg width="${width}" height="${height}" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" fill="url(#bangaPattern-${patternType})" color="${color}"/>
            <defs>
                <pattern id="bangaPattern-${patternType}" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                    <!-- Thick border grid -->
                    <rect width="50" height="50" fill="none" stroke="#333333" stroke-width="2"/>
                    
                    <!-- Pixelated circular pattern - woven effect -->
                    <!-- Outer ring -->
                    <rect x="5" y="22" width="4" height="6" fill="currentColor"/>
                    <rect x="6" y="18" width="4" height="4" fill="currentColor"/>
                    <rect x="8" y="15" width="6" height="3" fill="currentColor"/>
                    <rect x="12" y="13" width="8" height="2" fill="currentColor"/>
                    <rect x="18" y="12" width="14" height="2" fill="currentColor"/>
                    <rect x="30" y="13" width="8" height="2" fill="currentColor"/>
                    <rect x="36" y="15" width="6" height="3" fill="currentColor"/>
                    <rect x="40" y="18" width="4" height="4" fill="currentColor"/>
                    <rect x="41" y="22" width="4" height="6" fill="currentColor"/>
                    
                    <!-- Inner ring - accent -->
                    <rect x="15" y="22" width="4" height="6" fill="#DAA520"/>
                    <rect x="16" y="19" width="4" height="3" fill="#DAA520"/>
                    <rect x="18" y="17" width="6" height="2" fill="#DAA520"/>
                    <rect x="22" y="16" width="6" height="2" fill="#DAA520"/>
                    <rect x="26" y="17" width="6" height="2" fill="#DAA520"/>
                    <rect x="30" y="19" width="4" height="3" fill="#DAA520"/>
                    <rect x="31" y="22" width="4" height="6" fill="#DAA520"/>
                    
                    <!-- Center -->
                    <rect x="21" y="23" width="8" height="4" fill="currentColor"/>
                    <rect x="22" y="27" width="6" height="2" fill="currentColor"/>
                    
                    <!-- Woven petal details -->
                    <rect x="23" y="10" width="4" height="4" fill="#8B4513"/>
                    <rect x="38" y="23" width="4" height="4" fill="#8B4513"/>
                    <rect x="23" y="36" width="4" height="4" fill="#8B4513"/>
                    <rect x="8" y="23" width="4" height="4" fill="#8B4513"/>
                </pattern>
            </defs>
        </svg>`,
        'kabkab': `<svg width="${width}" height="${height}" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" fill="url(#kabkabPattern-${patternType})" color="${color}"/>
            <defs>
                <pattern id="kabkabPattern-${patternType}" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                    <!-- Thick border grid -->
                    <rect width="50" height="50" fill="none" stroke="#333333" stroke-width="2"/>
                    
                    <!-- Pixelated 8-pointed star - woven effect -->
                    <!-- Top point -->
                    <rect x="23" y="5" width="4" height="4" fill="currentColor"/>
                    <rect x="21" y="9" width="8" height="4" fill="currentColor"/>
                    <rect x="19" y="13" width="12" height="4" fill="currentColor"/>
                    
                    <!-- Top-right -->
                    <rect x="31" y="15" width="4" height="4" fill="currentColor"/>
                    <rect x="33" y="19" width="4" height="8" fill="currentColor"/>
                    <rect x="33" y="27" width="4" height="12" fill="currentColor"/>
                    
                    <!-- Right -->
                    <rect x="37" y="21" width="4" height="4" fill="currentColor"/>
                    <rect x="41" y="19" width="4" height="8" fill="currentColor"/>
                    <rect x="41" y="27" width="4" height="12" fill="currentColor"/>
                    
                    <!-- Bottom-right -->
                    <rect x="37" y="37" width="4" height="4" fill="currentColor"/>
                    <rect x="33" y="33" width="4" height="8" fill="currentColor"/>
                    <rect x="33" y="41" width="4" height="4" fill="currentColor"/>
                    
                    <!-- Bottom -->
                    <rect x="23" y="41" width="4" height="4" fill="currentColor"/>
                    <rect x="21" y="37" width="8" height="4" fill="currentColor"/>
                    <rect x="19" y="33" width="12" height="4" fill="currentColor"/>
                    
                    <!-- Bottom-left -->
                    <rect x="15" y="37" width="4" height="4" fill="currentColor"/>
                    <rect x="13" y="33" width="4" height="8" fill="currentColor"/>
                    <rect x="13" y="41" width="4" height="4" fill="currentColor"/>
                    
                    <!-- Left -->
                    <rect x="9" y="21" width="4" height="4" fill="currentColor"/>
                    <rect x="5" y="19" width="4" height="8" fill="currentColor"/>
                    <rect x="5" y="27" width="4" height="12" fill="currentColor"/>
                    
                    <!-- Top-left -->
                    <rect x="15" y="15" width="4" height="4" fill="currentColor"/>
                    <rect x="13" y="19" width="4" height="8" fill="currentColor"/>
                    <rect x="13" y="27" width="4" height="12" fill="currentColor"/>
                    
                    <!-- Center hexagon - accent -->
                    <rect x="21" y="21" width="8" height="4" fill="#DAA520"/>
                    <rect x="19" y="25" width="12" height="4" fill="#DAA520"/>
                    <rect x="21" y="29" width="8" height="4" fill="#DAA520"/>
                    
                    <!-- Center -->
                    <rect x="23" y="25" width="4" height="4" fill="currentColor"/>
                    
                    <!-- Woven corner dots -->
                    <rect x="23" y="13" width="4" height="4" fill="#8B4513"/>
                    <rect x="37" y="25" width="4" height="4" fill="#8B4513"/>
                    <rect x="23" y="37" width="4" height="4" fill="#8B4513"/>
                    <rect x="9" y="25" width="4" height="4" fill="#8B4513"/>
                </pattern>
            </defs>
        </svg>`,
        'sinag': `<svg width="${width}" height="${height}" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" fill="url(#sinagPattern-${patternType})" color="${color}"/>
            <defs>
                <pattern id="sinagPattern-${patternType}" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                    <!-- Thick border grid -->
                    <rect width="50" height="50" fill="none" stroke="#333333" stroke-width="2"/>
                    
                    <!-- Pixelated sun pattern - woven effect -->
                    <!-- Center sun -->
                    <rect x="21" y="21" width="8" height="8" fill="currentColor"/>
                    <rect x="23" y="23" width="4" height="4" fill="#DAA520"/>
                    
                    <!-- Main rays (cardinal directions) -->
                    <!-- Top -->
                    <rect x="23" y="5" width="4" height="8" fill="currentColor"/>
                    <rect x="23" y="13" width="4" height="4" fill="currentColor"/>
                    
                    <!-- Bottom -->
                    <rect x="23" y="37" width="4" height="8" fill="currentColor"/>
                    <rect x="23" y="33" width="4" height="4" fill="currentColor"/>
                    
                    <!-- Left -->
                    <rect x="5" y="23" width="8" height="4" fill="currentColor"/>
                    <rect x="13" y="23" width="4" height="4" fill="currentColor"/>
                    
                    <!-- Right -->
                    <rect x="37" y="23" width="8" height="4" fill="currentColor"/>
                    <rect x="33" y="23" width="4" height="4" fill="currentColor"/>
                    
                    <!-- Diagonal rays - woven texture -->
                    <!-- Top-right -->
                    <rect x="31" y="11" width="4" height="4" fill="#DAA520"/>
                    <rect x="35" y="15" width="4" height="4" fill="#DAA520"/>
                    <rect x="37" y="17" width="4" height="4" fill="#DAA520"/>
                    
                    <!-- Bottom-right -->
                    <rect x="35" y="31" width="4" height="4" fill="#DAA520"/>
                    <rect x="37" y="33" width="4" height="4" fill="#DAA520"/>
                    <rect x="39" y="35" width="4" height="4" fill="#DAA520"/>
                    
                    <!-- Bottom-left -->
                    <rect x="15" y="31" width="4" height="4" fill="#DAA520"/>
                    <rect x="11" y="33" width="4" height="4" fill="#DAA520"/>
                    <rect x="9" y="35" width="4" height="4" fill="#DAA520"/>
                    
                    <!-- Top-left -->
                    <rect x="11" y="11" width="4" height="4" fill="#DAA520"/>
                    <rect x="9" y="15" width="4" height="4" fill="#DAA520"/>
                    <rect x="7" y="17" width="4" height="4" fill="#DAA520"/>
                    
                    <!-- Additional diagonal details -->
                    <rect x="27" y="13" width="4" height="4" fill="#8B4513"/>
                    <rect x="33" y="19" width="4" height="4" fill="#8B4513"/>
                    <rect x="33" y="27" width="4" height="4" fill="#8B4513"/>
                    <rect x="27" y="33" width="4" height="4" fill="#8B4513"/>
                    <rect x="19" y="33" width="4" height="4" fill="#8B4513"/>
                    <rect x="13" y="27" width="4" height="4" fill="#8B4513"/>
                    <rect x="13" y="19" width="4" height="4" fill="#8B4513"/>
                    <rect x="19" y="13" width="4" height="4" fill="#8B4513"/>
                </pattern>
            </defs>
        </svg>`,
        'alon': `<svg width="${width}" height="${height}" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" fill="url(#alonPattern-${patternType})" color="${color}"/>
            <defs>
                <pattern id="alonPattern-${patternType}" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                    <!-- Thick border grid -->
                    <rect width="50" height="50" fill="none" stroke="#333333" stroke-width="2"/>
                    
                    <!-- Pixelated wave pattern - woven effect -->
                    <!-- Layer 1 zigzag - main wave -->
                    <rect x="2" y="23" width="4" height="4" fill="currentColor"/>
                    <rect x="6" y="19" width="4" height="4" fill="currentColor"/>
                    <rect x="10" y="15" width="4" height="4" fill="currentColor"/>
                    <rect x="14" y="11" width="4" height="4" fill="currentColor"/>
                    <rect x="18" y="7" width="4" height="4" fill="currentColor"/>
                    <rect x="22" y="11" width="4" height="4" fill="currentColor"/>
                    <rect x="26" y="15" width="4" height="4" fill="currentColor"/>
                    <rect x="30" y="19" width="4" height="4" fill="currentColor"/>
                    <rect x="34" y="23" width="4" height="4" fill="currentColor"/>
                    <rect x="38" y="27" width="4" height="4" fill="currentColor"/>
                    <rect x="42" y="31" width="4" height="4" fill="currentColor"/>
                    <rect x="46" y="35" width="4" height="4" fill="currentColor"/>
                    
                    <!-- Continue wave down -->
                    <rect x="42" y="39" width="4" height="4" fill="currentColor"/>
                    <rect x="38" y="43" width="4" height="4" fill="currentColor"/>
                    <rect x="34" y="47" width="4" height="4" fill="currentColor"/>
                    <rect x="30" y="43" width="4" height="4" fill="currentColor"/>
                    <rect x="26" y="39" width="4" height="4" fill="currentColor"/>
                    <rect x="22" y="35" width="4" height="4" fill="currentColor"/>
                    <rect x="18" y="31" width="4" height="4" fill="currentColor"/>
                    <rect x="14" y="27" width="4" height="4" fill="currentColor"/>
                    <rect x="10" y="23" width="4" height="4" fill="currentColor"/>
                    <rect x="6" y="27" width="4" height="4" fill="currentColor"/>
                    <rect x="2" y="31" width="4" height="4" fill="currentColor"/>
                    
                    <!-- Layer 2 zigzag - accent wave (offset) -->
                    <rect x="10" y="19" width="4" height="4" fill="#DAA520"/>
                    <rect x="14" y="15" width="4" height="4" fill="#DAA520"/>
                    <rect x="18" y="11" width="4" height="4" fill="#DAA520"/>
                    <rect x="22" y="15" width="4" height="4" fill="#DAA520"/>
                    <rect x="26" y="19" width="4" height="4" fill="#DAA520"/>
                    <rect x="30" y="23" width="4" height="4" fill="#DAA520"/>
                    <rect x="34" y="27" width="4" height="4" fill="#DAA520"/>
                    <rect x="38" y="31" width="4" height="4" fill="#DAA520"/>
                    <rect x="42" y="35" width="4" height="4" fill="#DAA520"/>
                    <rect x="46" y="39" width="4" height="4" fill="#DAA520"/>
                    
                    <!-- Continue accent wave -->
                    <rect x="42" y="43" width="4" height="4" fill="#DAA520"/>
                    <rect x="38" y="47" width="4" height="4" fill="#DAA520"/>
                    <rect x="34" y="43" width="4" height="4" fill="#DAA520"/>
                    <rect x="30" y="39" width="4" height="4" fill="#DAA520"/>
                    <rect x="26" y="35" width="4" height="4" fill="#DAA520"/>
                    <rect x="22" y="31" width="4" height="4" fill="#DAA520"/>
                    <rect x="18" y="27" width="4" height="4" fill="#DAA520"/>
                    <rect x="14" y="23" width="4" height="4" fill="#DAA520"/>
                    <rect x="10" y="27" width="4" height="4" fill="#DAA520"/>
                    <rect x="6" y="31" width="4" height="4" fill="#DAA520"/>
                    
                    <!-- Layer 3 zigzag - woven texture (offset) -->
                    <rect x="6" y="23" width="4" height="4" fill="#8B4513"/>
                    <rect x="10" y="19" width="4" height="4" fill="#8B4513"/>
                    <rect x="14" y="15" width="4" height="4" fill="#8B4513"/>
                    <rect x="18" y="11" width="4" height="4" fill="#8B4513"/>
                    <rect x="22" y="15" width="4" height="4" fill="#8B4513"/>
                    <rect x="26" y="19" width="4" height="4" fill="#8B4513"/>
                    <rect x="30" y="23" width="4" height="4" fill="#8B4513"/>
                    <rect x="34" y="27" width="4" height="4" fill="#8B4513"/>
                    <rect x="38" y="31" width="4" height="4" fill="#8B4513"/>
                    <rect x="42" y="35" width="4" height="4" fill="#8B4513"/>
                    <rect x="46" y="39" width="4" height="4" fill="#8B4513"/>
                </pattern>
            </defs>
        </svg>`,
        'dalisay': `<svg width="${width}" height="${height}" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <rect width="100" height="100" fill="url(#dalisayPattern-${patternType})" color="${color}"/>
            <defs>
                <pattern id="dalisayPattern-${patternType}" x="0" y="0" width="60" height="60" patternUnits="userSpaceOnUse">
                    <!-- Thick border grid -->
                    <rect width="60" height="60" fill="none" stroke="#333333" stroke-width="3"/>
                    
                    <!-- Large diamond motif - center -->
                    <!-- Top point -->
                    <rect x="28" y="5" width="4" height="4" fill="currentColor"/>
                    <rect x="26" y="9" width="8" height="4" fill="currentColor"/>
                    <rect x="24" y="13" width="12" height="4" fill="currentColor"/>
                    <rect x="22" y="17" width="16" height="4" fill="currentColor"/>
                    <rect x="20" y="21" width="20" height="4" fill="currentColor"/>
                    <rect x="18" y="25" width="24" height="4" fill="currentColor"/>
                    
                    <!-- Middle section -->
                    <rect x="16" y="29" width="28" height="4" fill="currentColor"/>
                    <rect x="14" y="33" width="32" height="4" fill="currentColor"/>
                    <rect x="12" y="37" width="36" height="4" fill="currentColor"/>
                    <rect x="10" y="41" width="40" height="4" fill="currentColor"/>
                    <rect x="8" y="45" width="44" height="4" fill="currentColor"/>
                    <rect x="6" y="49" width="48" height="4" fill="currentColor"/>
                    
                    <!-- Bottom half -->
                    <rect x="8" y="53" width="44" height="4" fill="currentColor"/>
                    <rect x="10" y="57" width="40" height="4" fill="currentColor"/>
                    
                    <!-- Inner diamond - accent pattern -->
                    <rect x="26" y="21" width="8" height="4" fill="#DAA520"/>
                    <rect x="24" y="25" width="12" height="4" fill="#DAA520"/>
                    <rect x="22" y="29" width="16" height="4" fill="#DAA520"/>
                    <rect x="20" y="33" width="20" height="4" fill="#DAA520"/>
                    <rect x="18" y="37" width="24" height="4" fill="#DAA520"/>
                    <rect x="20" y="41" width="20" height="4" fill="#DAA520"/>
                    <rect x="22" y="45" width="16" height="4" fill="#DAA520"/>
                    <rect x="24" y="49" width="12" height="4" fill="#DAA520"/>
                    <rect x="26" y="53" width="8" height="4" fill="#DAA520"/>
                    
                    <!-- Center detail -->
                    <rect x="28" y="33" width="4" height="4" fill="currentColor"/>
                    
                    <!-- Corner woven accents -->
                    <rect x="28" y="13" width="4" height="4" fill="#8B4513"/>
                    <rect x="45" y="30" width="4" height="4" fill="#8B4513"/>
                    <rect x="28" y="47" width="4" height="4" fill="#8B4513"/>
                    <rect x="11" y="30" width="4" height="4" fill="#8B4513"/>
                    
                    <!-- Additional woven details -->
                    <rect x="38" y="20" width="4" height="4" fill="#8B4513"/>
                    <rect x="18" y="20" width="4" height="4" fill="#8B4513"/>
                    <rect x="38" y="40" width="4" height="4" fill="#8B4513"/>
                    <rect x="18" y="40" width="4" height="4" fill="#8B4513"/>
                    
                    <!-- Horizontal connecting lines for tile effect -->
                    <rect x="0" y="29" width="6" height="2" fill="currentColor"/>
                    <rect x="54" y="29" width="6" height="2" fill="currentColor"/>
                    <rect x="0" y="33" width="6" height="2" fill="currentColor"/>
                    <rect x="54" y="33" width="6" height="2" fill="currentColor"/>
                </pattern>
            </defs>
        </svg>`
    };
    
    return svgTemplates[patternType] || svgTemplates['sussuh'];
}

function setColor(color) {
    selectedColor = color;
    
    // Update current color display with animation
    const colorDisplay = document.getElementById('currentColorDisplay');
    if (colorDisplay) {
        colorDisplay.style.backgroundColor = color;
        colorDisplay.style.animation = 'colorPulse 0.3s ease-out';
    }
    
    // Update color hex input
    const colorHex = document.getElementById('colorHex');
    if (colorHex) {
        colorHex.value = color;
    }
    
    // Update all SVG pattern colors
    updateAllPatternColors(color);
    
    // Update selected pattern preview display (but don't add to canvas)
    if (selectedPattern) {
        const infoDiv = document.getElementById('selectedPatternInfo');
        const nameSpan = document.getElementById('selectedPatternName');
        const previewDiv = document.getElementById('selectedPatternPreview');
    }
    
    // Update color button highlights
    document.querySelectorAll('.color-btn').forEach(btn => {
        btn.classList.remove('ring-4', 'ring-purple-400', 'scale-110');
    });
    
    // Find and highlight the matching color button
    const matchingBtn = Array.from(document.querySelectorAll('.color-btn')).find(btn => {
        const bgColor = window.getComputedStyle(btn).backgroundColor;
        return rgbToHex(bgColor).toUpperCase() === color.toUpperCase();
    });
    
    if (matchingBtn) {
        matchingBtn.classList.add('ring-4', 'ring-purple-400', 'scale-110');
    }
    
    // Update custom color inputs
    const customColorInput = document.getElementById('customColor');
    const colorHexInput = document.getElementById('colorHex');
    if (customColorInput) customColorInput.value = color;
    if (colorHexInput) colorHexInput.value = color;
    
    // Redraw canvas to show color changes immediately
    if (livePreviewCanvas && livePreviewCtx) {
        redrawCanvas();
    }
    
    console.log('Color set to:', color, 'Canvas redrawn');
}

function rgbToHex(rgb) {
    const result = rgb.match(/\d+/g);
    if (!result) return '#000000';
    return "#" + ((1 << 24) + (parseInt(result[0]) << 16) + (parseInt(result[1]) << 8) + parseInt(result[2])).toString(16).slice(1);
}

function applyCustomColor() {
    const colorHex = document.getElementById('colorHex');
    if (colorHex && /^#[0-9A-F]{6}$/i.test(colorHex.value)) {
        setColor(colorHex.value);
    }
}


function animatePatternAddition(pattern) {
    let scale = 0;
    const targetScale = 1;
    const animationDuration = 300;
    const startTime = Date.now();
    
    function animate() {
        const elapsed = Date.now() - startTime;
        const progress = Math.min(elapsed / animationDuration, 1);
        
        scale = easeOutBack(progress);
        
        // Update pattern scale for animation
        pattern.scale = scale;
        
        // Clear and redraw canvas
        redrawCanvas();
        
        if (progress < 1) {
            requestAnimationFrame(animate);
        } else {
            // Ensure final scale is set correctly
            pattern.scale = 1;
            redrawCanvas();
        }
    }
    
    animate();
}

function easeOutBack(x) {
    const c1 = 1.70158;
    const c3 = c1 + 1;
    return 1 + c3 * Math.pow(x - 1, 3) + c1 * Math.pow(x - 1, 2);
}

// Pattern storage for live preview
let addedPatterns = [];
let patternIdCounter = 0;

function redrawCanvas() {
    console.log('redrawCanvas called');
    console.log('livePreviewCanvas:', livePreviewCanvas);
    console.log('livePreviewCtx:', livePreviewCtx);
    console.log('addedPatterns:', addedPatterns);
    
    if (!livePreviewCanvas || !livePreviewCtx) return;
    
    // Clear canvas
    clearCanvas();
    
    // Draw grid if enabled
    if (gridEnabled) {
        drawGrid();
    }
    
    // Redraw all stored patterns
    addedPatterns.forEach(entry => {
        drawPatternEntry(livePreviewCtx, entry);
    });
}

function drawPatternEntry(ctx, entry) {
    const originalFill = ctx.fillStyle;
    const originalStroke = ctx.strokeStyle;
    const { type, x, y, scale = 1 } = entry;
    ctx.fillStyle = entry.color || selectedColor;
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 1;
    ctx.globalAlpha = 1;
    if (type === 'image' && entry.imageSrc) {
        if (!entry._img) {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                entry._img = img;
                // Use requestAnimationFrame to avoid recursive loop
                requestAnimationFrame(() => redrawCanvas());
            };
            img.src = entry.imageSrc;
            // Will draw on next redraw when loaded
        } else {
            const w = (entry.width || 160) * scale;
            const h = (entry.height || 120) * scale;
            ctx.drawImage(entry._img, x - w / 2, y - h / 2, w, h);
        }
    } else {
        drawPattern(ctx, x, y, type, scale);
    }
    ctx.fillStyle = originalFill;
    ctx.strokeStyle = originalStroke;
}

function addImagePatternToCanvas(imageSrc) {
    if (!livePreviewCanvas || !livePreviewCtx || !imageSrc) return;
    const x = livePreviewCanvas.width / 2;
    const y = livePreviewCanvas.height / 2;
    const newPattern = {
        id: patternIdCounter++,
        type: 'image',
        x, y,
        color: selectedColor,
        scale: 1,
        imageSrc: imageSrc
    };
    addedPatterns.push(newPattern);
    animatePatternAddition(newPattern);
    patternCount++;
    const countEl = document.getElementById('patternCount');
    if (countEl) countEl.textContent = patternCount;
}

function showcasePatterns() {
    // Clear existing patterns
    addedPatterns = [];
    patternCount = 0;
    
    // Create a beautiful showcase of all patterns
    const showcaseData = [
        { type: 'sussuh', x: 80, y: 80, color: '#B22222' },
        { type: 'banga', x: 200, y: 80, color: '#DAA520' },
        { type: 'kabkab', x: 320, y: 80, color: '#8B4513' },
        { type: 'sinag', x: 80, y: 200, color: '#D2691E' },
        { type: 'alon', x: 200, y: 200, color: '#A0522D' },
        { type: 'dalisay', x: 320, y: 200, color: '#B22222' }
    ];
    
    // Add patterns with staggered animation
    showcaseData.forEach((data, index) => {
        setTimeout(() => {
            const pattern = {
                id: patternIdCounter++,
                type: data.type,
                x: data.x,
                y: data.y,
                color: data.color,
                scale: 1
            };
            addedPatterns.push(pattern);
            
            // Animate the addition
            animatePatternAddition(pattern);
            
            // Update pattern count
            patternCount++;
            document.getElementById('patternCount').textContent = patternCount;
            
            if (index === showcaseData.length - 1) {
                showNotification('Pattern showcase complete!', 'success');
            }
        }, index * 200);
    });
}

function clearCanvas() {
    if (!livePreviewCanvas || !livePreviewCtx) return;
    
    livePreviewCtx.clearRect(0, 0, livePreviewCanvas.width, livePreviewCanvas.height);
    
    // Draw background
    const gradient = livePreviewCtx.createLinearGradient(0, 0, livePreviewCanvas.width, livePreviewCanvas.height);
    gradient.addColorStop(0, '#f9fafb');
    gradient.addColorStop(1, '#f3f4f6');
    livePreviewCtx.fillStyle = gradient;
    livePreviewCtx.fillRect(0, 0, livePreviewCanvas.width, livePreviewCanvas.height);
}

function clearPreview() {
    clearCanvas();
    // Clear stored patterns and counters for a full reset
    addedPatterns = [];
    patternCount = 0;
    const countEl = document.getElementById('patternCount');
    if (countEl) countEl.textContent = patternCount;
    showNotification('Preview cleared', 'info');
}

function clearSelectedPattern() {
    selectedPattern = null;
    
    const infoDiv = document.getElementById('selectedPatternInfo');
    if (infoDiv) {
        infoDiv.style.animation = 'slideOutDown 0.3s ease-out';
        setTimeout(() => {
            infoDiv.classList.add('hidden');
            infoDiv.style.animation = '';
        }, 300);
    }
    
    // Remove all highlights
    document.querySelectorAll('.pattern-card').forEach(card => {
        card.classList.remove('border-purple-600', 'bg-purple-50', 'ring-4', 'ring-purple-200');
        card.classList.add('border-gray-200');
    });
}

function downloadPreview() {
    if (!livePreviewCanvas) return;
    
    const link = document.createElement('a');
    link.download = `yakan-pattern-${Date.now()}.png`;
    link.href = livePreviewCanvas.toDataURL();
    link.click();
    
    showNotification('Pattern downloaded!', 'success');
}

function randomizePattern() {
    const patternKeys = Object.keys(patterns);
    const randomPattern = patternKeys[Math.floor(Math.random() * patternKeys.length)];
    const randomColors = ['#8B4513', '#D2691E', '#A0522D', '#FFD700', '#4682B4', '#FF69B4', '#000000', '#8B0000', '#4B0082'];
    const randomColor = randomColors[Math.floor(Math.random() * randomColors.length)];
    
    selectPattern(randomPattern);
    setColor(randomColor);
    
    showNotification(`Randomized: ${patterns[randomPattern].name}`, 'info');
}

function toggleGrid() {
    gridEnabled = !gridEnabled;
    redrawCanvas();
    showNotification(gridEnabled ? 'Grid enabled' : 'Grid disabled', 'info');
}

function drawGrid() {
    if (!livePreviewCanvas || !livePreviewCtx) return;
    
    const gridSize = 20;
    livePreviewCtx.strokeStyle = 'rgba(156, 163, 175, 0.3)';
    livePreviewCtx.lineWidth = 1;
    
    // Draw vertical lines
    for (let x = 0; x <= livePreviewCanvas.width; x += gridSize) {
        livePreviewCtx.beginPath();
        livePreviewCtx.moveTo(x, 0);
        livePreviewCtx.lineTo(x, livePreviewCanvas.height);
        livePreviewCtx.stroke();
    }
    
    // Draw horizontal lines
    for (let y = 0; y <= livePreviewCanvas.height; y += gridSize) {
        livePreviewCtx.beginPath();
        livePreviewCtx.moveTo(0, y);
        livePreviewCtx.lineTo(livePreviewCanvas.width, y);
        livePreviewCtx.stroke();
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    
    const colors = {
        success: 'bg-green-500 text-white',
        warning: 'bg-yellow-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white'
    };
    
    notification.classList.add(...colors[type].split(' '));
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Slide in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Slide out and remove
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

function startAnimations() {
    // Add any continuous animations here
}

function saveAsDraft() {
    // Save current pattern selections
    const designData = {
        selectedPattern: selectedPattern,
        selectedColor: selectedColor,
        patternCount: patternCount,
        timestamp: new Date().toISOString()
    };
    
    console.log('Draft saved:', designData);
    showNotification('Design saved as draft!', 'success');
}

function showColorPicker() {
    document.getElementById('customColor').click();
}
</script>

@push('styles')
<style>
/* Custom scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Tool button styles */
.tool-btn.active {
    background: linear-gradient(to right, #9333ea, #3b82f6);
    color: white;
}

.color-btn.active div {
    border-color: #9333ea;
    border-width: 3px;
}

/* Canvas background */
#designCanvas {
    background-image: 
        linear-gradient(45deg, #f0f0f0 25%, transparent 25%),
        linear-gradient(-45deg, #f0f0f0 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #f0f0f0 75%),
        linear-gradient(-45deg, transparent 75%, #f0f0f0 75%);
    background-size: 20px 20px;
    background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
}

/* Advanced animations */
@keyframes slideInUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideOutDown {
    from {
        transform: translateY(0);
        opacity: 1;
    }
    to {
        transform: translateY(100%);
        opacity: 0;
    }
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutLeft {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(-100%);
        opacity: 0;
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

@keyframes bounce {
    0%, 20%, 53%, 80%, 100% {
        transform: translate3d(0, 0, 0);
    }
    40%, 43% {
        transform: translate3d(0, -30px, 0);
    }
    70% {
        transform: translate3d(0, -15px, 0);
    }
    90% {
        transform: translate3d(0, -4px, 0);
    }
}

@keyframes colorPulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(147, 51, 234, 0.7);
    }
    50% {
        transform: scale(1.1);
        box-shadow: 0 0 0 10px rgba(147, 51, 234, 0);
    }
}

@keyframes glow {
    0%, 100% {
        box-shadow: 0 0 20px rgba(147, 51, 234, 0.5);
    }
    50% {
        box-shadow: 0 0 30px rgba(147, 51, 234, 0.8);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes shimmer {
    0% {
        background-position: -1000px 0;
    }
    100% {
        background-position: 1000px 0;
    }
}

/* Enhanced pattern card styles */
.pattern-card {
    position: relative;
    overflow: hidden;
}

.pattern-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.pattern-card:hover::before {
    left: 100%;
}

.pattern-card.selected {
    border-color: #9333ea !important;
    background: linear-gradient(135deg, rgba(147, 51, 234, 0.1), rgba(59, 130, 246, 0.1));
    box-shadow: 0 0 30px rgba(147, 51, 234, 0.3);
}

/* Color button enhancements */
.color-btn {
    position: relative;
    overflow: hidden;
}

.color-btn::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.color-btn:active::after {
    width: 300px;
    height: 300px;
}

/* Live preview canvas enhancements */
#livePreview {
    transition: all 0.3s ease;
    cursor: crosshair;
}

#livePreview:hover {
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    transform: scale(1.02);
}

/* Notification styles */
.notification-enter {
    animation: slideInRight 0.3s ease-out;
}

.notification-exit {
    animation: slideOutLeft 0.3s ease-out;
}

/* Loading animation */
@keyframes loading {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.loading-spinner {
    animation: loading 1s linear infinite;
}

/* Gradient text effects */
.gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Glassmorphism effects */
.glass {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Particle effects */
@keyframes particle-float {
    0% {
        transform: translateY(100vh) rotate(0deg);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    90% {
        opacity: 1;
    }
    100% {
        transform: translateY(-100vh) rotate(720deg);
        opacity: 0;
    }
}

.particle {
    position: fixed;
    pointer-events: none;
    opacity: 0;
    animation: particle-float 10s linear infinite;
}

/* Responsive enhancements */
@media (max-width: 768px) {
    .pattern-card {
        transform: scale(0.9);
    }
    
    .pattern-card:hover {
        transform: scale(0.95);
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .pattern-card {
        background: #1f2937;
        border-color: #374151;
    }
    
    .pattern-card:hover {
        background: #374151;
    }
}

/* Accessibility enhancements */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Focus styles for accessibility */
.pattern-card:focus,
.color-btn:focus,
button:focus {
    outline: 3px solid #9333ea;
    outline-offset: 2px;
}

/* Print styles */
@media print {
    .no-print {
        display: none !important;
    }
    
    #livePreview {
        border: 2px solid #000;
    }
}

/* Custom selection colors */
::selection {
    background: rgba(147, 51, 234, 0.3);
}

::-moz-selection {
    background: rgba(147, 51, 234, 0.3);
}

/* Smooth scrolling */
html {
    scroll-behavior: smooth;
}

/* Enhanced button styles */
.btn-enhanced {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.btn-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s;
}

.btn-enhanced:hover::before {
    left: 100%;
}

/* Tooltip styles */
.tooltip {
    position: relative;
}

.tooltip::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #1f2937;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
    z-index: 50;
}

.tooltip:hover::after {
    opacity: 1;
}

/* Skeleton loading */
@keyframes skeleton-loading {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
}

/* Progress bar */
.progress-bar {
    height: 4px;
    background: linear-gradient(90deg, #9333ea 0%, #3b82f6 50%, #9333ea 100%);
    background-size: 200% 100%;
    animation: shimmer 2s linear infinite;
}

/* Badge animations */
.badge-pulse {
    animation: pulse 2s infinite;
}

/* Floating action button */
.fab {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #9333ea, #3b82f6);
    color: white;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(147, 51, 234, 0.3);
    transition: all 0.3s ease;
    z-index: 40;
}

.fab:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 30px rgba(147, 51, 234, 0.4);
}

/* Modal styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 50;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal-overlay.active {
    opacity: 1;
}

.modal-content {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.9);
    background: white;
    border-radius: 1rem;
    padding: 2rem;
    max-width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 60;
    opacity: 0;
    transition: all 0.3s ease;
}

.modal-overlay.active .modal-content {
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
}
</style>
@endpush

@push('scripts')
<!-- No designer script needed - step simplified -->
@endpush
@endsection
