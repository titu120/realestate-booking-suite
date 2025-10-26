<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<style>
    * {
        font-family: 'Inter', sans-serif;
    }
    
    body {
        overflow-x: hidden;
    }

    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #0f766e;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #0d5c54;
    }

    /* Smooth Animations */
    .property-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .property-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .property-card img {
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .property-card:hover img {
        transform: scale(1.1);
    }

    /* Badge Animations */
    .badge-pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
        }
    }

    /* Map Markers */
    .map-marker {
        position: absolute;
        transform: translate(-50%, -100%);
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 10;
    }

    .map-marker:hover {
        z-index: 20;
        transform: translate(-50%, -100%) scale(1.15);
    }

    .map-marker.active {
        z-index: 30;
        transform: translate(-50%, -100%) scale(1.2);
    }

    /* Tooltip */
    .marker-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-10px);
        background: white;
        padding: 12px 16px;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: all 0.3s ease;
        margin-bottom: 10px;
    }

    .map-marker:hover .marker-tooltip {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    /* Dropdown Animation */
    .dropdown-content {
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Glass Effect */
    .glass-effect {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    /* Gradient Overlay */
    .gradient-overlay {
        background: linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(0,0,0,0.7) 100%);
    }

    /* Loading Animation */
    .loading-spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #0f766e;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Filter Chip */
    .filter-chip {
        transition: all 0.3s ease;
    }

    .filter-chip:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Map Container */
    .map-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }

    .map-bg {
        background-image: 
            radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 40% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
        width: 100%;
        height: 100%;
    }

    /* View Toggle */
    .view-toggle button {
        transition: all 0.3s ease;
    }

    .view-toggle button.active {
        background: #0f766e;
        color: white;
    }

    /* Mobile Menu */
    .mobile-menu {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .mobile-menu.active {
        transform: translateX(0);
    }

    /* Skeleton Loading */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Price Range Slider */
    input[type="range"]::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        background: #0f766e;
        cursor: pointer;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    input[type="range"]::-moz-range-thumb {
        width: 20px;
        height: 20px;
        background: #0f766e;
        cursor: pointer;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Search Input Focus */
    .search-input:focus {
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
    }

    /* Smooth Page Transitions */
    .page-transition {
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<!-- External Dependencies -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<div class="bg-gray-50">
    


    <!-- Advanced Search Bar -->
    <div class="bg-white border-b border-gray-200 py-6">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Search Input -->
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input 
                        type="text" 
                        placeholder="Address, City, ZIP..." 
                        class="w-full pl-12 pr-4 py-3.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 search-input transition"
                        id="searchInput"
                    >
                </div>

                <!-- Filter Buttons -->
                <div class="flex flex-wrap gap-3 relative">
                    <button onclick="toggleDropdown('priceDropdown')" class="filter-chip px-5 py-3 bg-white border border-gray-300 rounded-xl font-medium text-gray-700 hover:border-teal-700 hover:text-teal-700 transition flex items-center space-x-2">
                        <span>Price</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>

                    <button onclick="toggleDropdown('typeDropdown')" class="filter-chip px-5 py-3 bg-white border border-gray-300 rounded-xl font-medium text-gray-700 hover:border-teal-700 hover:text-teal-700 transition flex items-center space-x-2">
                        <span>Type</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>

                    <button onclick="toggleDropdown('bedroomsDropdown')" class="filter-chip px-5 py-3 bg-white border border-gray-300 rounded-xl font-medium text-gray-700 hover:border-teal-700 hover:text-teal-700 transition flex items-center space-x-2">
                        <span>Bedrooms</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>

                    <button onclick="toggleDropdown('bathroomsDropdown')" class="filter-chip px-5 py-3 bg-white border border-gray-300 rounded-xl font-medium text-gray-700 hover:border-teal-700 hover:text-teal-700 transition flex items-center space-x-2">
                        <span>Bathrooms</span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>

                    <button onclick="toggleDropdown('moreFiltersDropdown')" class="filter-chip px-5 py-3 bg-white border border-gray-300 rounded-xl font-medium text-gray-700 hover:border-teal-700 hover:text-teal-700 transition flex items-center space-x-2">
                        <span>More filters</span>
                        <i class="fas fa-sliders-h text-sm"></i>
                    </button>

                    <button class="px-5 py-3 bg-teal-700 text-white rounded-xl font-medium hover:bg-teal-800 transition shadow-md hover:shadow-lg">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                </div>
            </div>

            <!-- Dropdown Panels Container -->
            <div class="relative mt-4">
                <!-- Price Dropdown -->
                <div id="priceDropdown" class="dropdown-content hidden absolute left-0 right-0 z-50 bg-white border border-gray-200 rounded-xl p-6 shadow-2xl">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Min Price</label>
                            <input type="text" placeholder="$ Min Price" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Price</label>
                            <input type="text" placeholder="$ Max Price" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>
                </div>

                <!-- Type Dropdown -->
                <div id="typeDropdown" class="dropdown-content hidden absolute left-0 right-0 z-50 bg-white border border-gray-200 rounded-xl p-6 shadow-2xl">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-teal-700 transition">
                            <input type="checkbox" class="w-4 h-4 text-teal-700 rounded focus:ring-teal-500">
                            <span class="text-gray-700 font-medium">House</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-teal-700 transition">
                            <input type="checkbox" class="w-4 h-4 text-teal-700 rounded focus:ring-teal-500">
                            <span class="text-gray-700 font-medium">Apartment</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-teal-700 transition">
                            <input type="checkbox" class="w-4 h-4 text-teal-700 rounded focus:ring-teal-500">
                            <span class="text-gray-700 font-medium">Condo</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-teal-700 transition">
                            <input type="checkbox" class="w-4 h-4 text-teal-700 rounded focus:ring-teal-500">
                            <span class="text-gray-700 font-medium">Office</span>
                        </label>
                    </div>
                </div>

                <!-- Bedrooms Dropdown -->
                <div id="bedroomsDropdown" class="dropdown-content hidden absolute left-0 right-0 z-50 bg-white border border-gray-200 rounded-xl p-6 shadow-2xl">
                    <div class="flex flex-wrap gap-2">
                        <button class="px-6 py-2.5 border-2 border-gray-300 rounded-lg hover:bg-teal-700 hover:text-white hover:border-teal-700 transition font-medium">Any</button>
                        <button class="px-6 py-2.5 border-2 border-gray-300 rounded-lg hover:bg-teal-700 hover:text-white hover:border-teal-700 transition font-medium">1+</button>
                        <button class="px-6 py-2.5 border-2 border-gray-300 rounded-lg hover:bg-teal-700 hover:text-white hover:border-teal-700 transition font-medium">2+</button>
                        <button class="px-6 py-2.5 border-2 border-gray-300 rounded-lg hover:bg-teal-700 hover:text-white hover:border-teal-700 transition font-medium">3+</button>
                        <button class="px-6 py-2.5 border-2 border-gray-300 rounded-lg hover:bg-teal-700 hover:text-white hover:border-teal-700 transition font-medium">4+</button>
                        <button class="px-6 py-2.5 border-2 border-gray-300 rounded-lg hover:bg-teal-700 hover:text-white hover:border-teal-700 transition font-medium">5+</button>
                    </div>
                </div>

                <!-- Bathrooms Dropdown -->
                <div id="bathroomsDropdown" class="dropdown-content hidden absolute left-0 right-0 z-50 bg-white border border-gray-200 rounded-xl p-6 shadow-2xl">
                    <div class="flex flex-wrap gap-2">
                        <button class="px-6 py-2.5 border-2 border-gray-300 rounded-lg hover:bg-teal-700 hover:text-white hover:border-teal-700 transition font-medium">Any</button>
                        <button class="px-6 py-2.5 border-2 border-gray-300 rounded-lg hover:bg-teal-700 hover:text-white hover:border-teal-700 transition font-medium">1+</button>
                        <button class="px-6 py-2.5 border-2 border-gray-300 rounded-lg hover:bg-teal-700 hover:text-white hover:border-teal-700 transition font-medium">2+</button>
                        <button class="px-6 py-2.5 border-2 border-gray-300 rounded-lg hover:bg-teal-700 hover:text-white hover:border-teal-700 transition font-medium">3+</button>
                        <button class="px-6 py-2.5 border-2 border-gray-300 rounded-lg hover:bg-teal-700 hover:text-white hover:border-teal-700 transition font-medium">4+</button>
                    </div>
                </div>

                <!-- More Filters Dropdown -->
                <div id="moreFiltersDropdown" class="dropdown-content hidden absolute left-0 right-0 z-50 bg-white border border-gray-200 rounded-xl p-6 shadow-2xl">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Square Feet</label>
                            <div class="flex gap-2">
                                <input type="number" placeholder="Min" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <input type="number" placeholder="Max" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Year Built</label>
                            <select class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <option>Any</option>
                                <option>2020+</option>
                                <option>2010+</option>
                                <option>2000+</option>
                                <option>1990+</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-500">
                                <option>All</option>
                                <option>For Sale</option>
                                <option>For Rent</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4 lg:px-6 py-6">
        <!-- Control Bar -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <!-- Left Side -->
            <div class="flex items-center space-x-4">
                <button onclick="toggleView('list')" class="flex items-center space-x-2 text-gray-600 hover:text-teal-700 transition">
                    <i class="fas fa-list text-xl"></i>
                    <span class="font-medium">List View</span>
                </button>
                <div class="text-gray-400">|</div>
                <button onclick="toggleView('map')" class="flex items-center space-x-2 text-teal-700 font-medium">
                    <i class="fas fa-map-marked-alt text-xl"></i>
                    <span>Map View</span>
                </button>
                <div class="px-4 py-2 bg-teal-50 text-teal-700 rounded-lg font-medium">
                    <span id="resultsCount">7</span> results
                </div>
            </div>

            <!-- Right Side -->
            <div class="flex items-center space-x-3">
                <span class="text-gray-600 font-medium">Sort by:</span>
                <select class="border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 font-medium">
                    <option>Newest</option>
                    <option>Price: Low to High</option>
                    <option>Price: High to Low</option>
                    <option>Most Popular</option>
                    <option>Recently Updated</option>
                </select>

                <div class="view-toggle flex bg-gray-100 rounded-lg p-1">
                    <button onclick="changeLayout('grid')" id="gridBtn" class="active px-4 py-2 rounded-lg">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button onclick="changeLayout('column')" id="columnBtn" class="px-4 py-2 rounded-lg">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <button class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-sliders-h"></i>
                </button>
            </div>
        </div>

        <!-- Map and Listings Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Property Listings -->
            <div class="custom-scrollbar overflow-y-auto" style="max-height: calc(100vh - 300px);" id="propertiesContainer">
                <div id="propertyGrid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Property Card 1 -->
                    <div class="property-card bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all" data-property-id="1">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800" alt="Property" class="w-full h-56 object-cover">
                            <div class="absolute inset-0 gradient-overlay"></div>
                            <div class="absolute top-4 left-4">
                                <span class="badge-pulse bg-orange-500 text-white px-4 py-1.5 rounded-full text-sm font-semibold">Featured</span>
                            </div>
                            <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-teal-700 hover:text-white transition shadow-lg">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="text-white font-bold text-xl mb-1">Alove Avenue</h3>
                                <p class="text-white/90 text-sm">Alove Avenue</p>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl font-bold text-teal-700">$450,000</span>
                                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">For sale</span>
                            </div>
                            <div class="flex items-center justify-between text-gray-600 text-sm mb-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bed"></i>
                                    <span>2 beds</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bath"></i>
                                    <span>2 baths</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>630 sq ft</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm text-gray-500">Apartment</span>
                                <button class="text-teal-700 font-medium hover:text-teal-800 transition">
                                    View Details <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Card 2 -->
                    <div class="property-card bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all" data-property-id="2">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800" alt="Property" class="w-full h-56 object-cover">
                            <div class="absolute inset-0 gradient-overlay"></div>
                            <div class="absolute top-4 left-4">
                                <span class="badge-pulse bg-green-500 text-white px-4 py-1.5 rounded-full text-sm font-semibold">Just listed</span>
                            </div>
                            <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-teal-700 hover:text-white transition shadow-lg">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="text-white font-bold text-xl mb-1">725 NE 168th St</h3>
                                <p class="text-white/90 text-sm">Miami, FL 33162</p>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl font-bold text-teal-700">$1,500</span>
                                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">For rent</span>
                            </div>
                            <div class="flex items-center justify-between text-gray-600 text-sm mb-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bed"></i>
                                    <span>3 beds</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bath"></i>
                                    <span>2 baths</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>850 sq ft</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm text-gray-500">Apartment</span>
                                <button class="text-teal-700 font-medium hover:text-teal-800 transition">
                                    View Details <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Card 3 -->
                    <div class="property-card bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all" data-property-id="3">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800" alt="Property" class="w-full h-56 object-cover">
                            <div class="absolute inset-0 gradient-overlay"></div>
                            <div class="absolute top-4 left-4">
                                <span class="badge-pulse bg-green-500 text-white px-4 py-1.5 rounded-full text-sm font-semibold">Just listed</span>
                            </div>
                            <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-teal-700 hover:text-white transition shadow-lg">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="text-white font-bold text-xl mb-1">261 SW 8th St</h3>
                                <p class="text-white/90 text-sm">Miami, FL 33130</p>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl font-bold text-teal-700">$220,000</span>
                                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">For sale</span>
                            </div>
                            <div class="flex items-center justify-between text-gray-600 text-sm mb-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bed"></i>
                                    <span>5 beds</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bath"></i>
                                    <span>2 baths</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>700 sq ft</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm text-gray-500">Condo</span>
                                <button class="text-teal-700 font-medium hover:text-teal-800 transition">
                                    View Details <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Card 4 -->
                    <div class="property-card bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all" data-property-id="4">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800" alt="Property" class="w-full h-56 object-cover">
                            <div class="absolute inset-0 gradient-overlay"></div>
                            <div class="absolute top-4 left-4">
                                <span class="badge-pulse bg-orange-500 text-white px-4 py-1.5 rounded-full text-sm font-semibold">Featured</span>
                            </div>
                            <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-teal-700 hover:text-white transition shadow-lg">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="text-white font-bold text-xl mb-1">1551 West Ave</h3>
                                <p class="text-white/90 text-sm">Miami Beach, FL 33139</p>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl font-bold text-teal-700">$459,000</span>
                                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">For sale</span>
                            </div>
                            <div class="flex items-center justify-between text-gray-600 text-sm mb-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bed"></i>
                                    <span>3 beds</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bath"></i>
                                    <span>2 baths</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>679 sq ft</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm text-gray-500">House</span>
                                <button class="text-teal-700 font-medium hover:text-teal-800 transition">
                                    View Details <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Card 5 -->
                    <div class="property-card bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all" data-property-id="5">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800" alt="Property" class="w-full h-56 object-cover">
                            <div class="absolute inset-0 gradient-overlay"></div>
                            <div class="absolute top-4 left-4">
                                <span class="badge-pulse bg-green-500 text-white px-4 py-1.5 rounded-full text-sm font-semibold">Just listed</span>
                            </div>
                            <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-teal-700 hover:text-white transition shadow-lg">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="text-white font-bold text-xl mb-1">8230 W Flagler St</h3>
                                <p class="text-white/90 text-sm">Miami, FL 33144</p>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl font-bold text-teal-700">$5,000</span>
                                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">For rent</span>
                            </div>
                            <div class="flex items-center justify-between text-gray-600 text-sm mb-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bed"></i>
                                    <span>3 beds</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bath"></i>
                                    <span>3 baths</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>350 sq ft</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm text-gray-500">Office</span>
                                <button class="text-teal-700 font-medium hover:text-teal-800 transition">
                                    View Details <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Card 6 -->
                    <div class="property-card bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all" data-property-id="6">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?w=800" alt="Property" class="w-full h-56 object-cover">
                            <div class="absolute inset-0 gradient-overlay"></div>
                            <div class="absolute top-4 left-4">
                                <span class="badge-pulse bg-green-500 text-white px-4 py-1.5 rounded-full text-sm font-semibold">Just listed</span>
                            </div>
                            <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-teal-700 hover:text-white transition shadow-lg">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="text-white font-bold text-xl mb-1">924 Marseille Dr</h3>
                                <p class="text-white/90 text-sm">Miami Beach, FL 33141</p>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl font-bold text-teal-700">$4,395,000</span>
                                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">For sale</span>
                            </div>
                            <div class="flex items-center justify-between text-gray-600 text-sm mb-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bed"></i>
                                    <span>5 beds</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bath"></i>
                                    <span>3 baths</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>1,200 sq ft</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm text-gray-500">Apartment</span>
                                <button class="text-teal-700 font-medium hover:text-teal-800 transition">
                                    View Details <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Card 7 -->
                    <div class="property-card bg-white rounded-2xl overflow-hidden shadow-md hover:shadow-2xl transition-all" data-property-id="7">
                        <div class="relative overflow-hidden">
                            <img src="https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800" alt="Property" class="w-full h-56 object-cover">
                            <div class="absolute inset-0 gradient-overlay"></div>
                            <div class="absolute top-4 left-4">
                                <span class="badge-pulse bg-orange-500 text-white px-4 py-1.5 rounded-full text-sm font-semibold">Featured</span>
                            </div>
                            <button class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center hover:bg-teal-700 hover:text-white transition shadow-lg">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="text-white font-bold text-xl mb-1">Luxury Penthouse</h3>
                                <p class="text-white/90 text-sm">Downtown Miami</p>
                            </div>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-2xl font-bold text-teal-700">$2,800,000</span>
                                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">For sale</span>
                            </div>
                            <div class="flex items-center justify-between text-gray-600 text-sm mb-4">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bed"></i>
                                    <span>4 beds</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-bath"></i>
                                    <span>4 baths</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>2,500 sq ft</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-sm text-gray-500">Penthouse</span>
                                <button class="text-teal-700 font-medium hover:text-teal-800 transition">
                                    View Details <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive Map -->
            <div class="relative rounded-2xl overflow-hidden shadow-lg sticky top-24" style="height: calc(100vh - 300px);">
                <div class="map-container h-full relative">
                    <div class="map-bg h-full relative">
                        <!-- Map Controls -->
                        <div class="absolute top-4 right-4 z-20 space-y-2">
                            <button class="w-10 h-10 bg-white rounded-lg shadow-lg flex items-center justify-center hover:bg-gray-50 transition">
                                <i class="fas fa-plus text-gray-700"></i>
                            </button>
                            <button class="w-10 h-10 bg-white rounded-lg shadow-lg flex items-center justify-center hover:bg-gray-50 transition">
                                <i class="fas fa-minus text-gray-700"></i>
                            </button>
                            <button class="w-10 h-10 bg-white rounded-lg shadow-lg flex items-center justify-center hover:bg-gray-50 transition">
                                <i class="fas fa-expand text-gray-700"></i>
                            </button>
                        </div>

                        <!-- Map Legend -->
                        <div class="absolute bottom-4 left-4 bg-white rounded-xl shadow-lg p-4 z-20">
                            <h4 class="font-semibold text-gray-900 mb-3">Legend</h4>
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                                    <span class="text-sm text-gray-600">Featured</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                    <span class="text-sm text-gray-600">Just Listed</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 bg-teal-700 rounded-full"></div>
                                    <span class="text-sm text-gray-600">Standard</span>
                                </div>
                            </div>
                        </div>

                        <!-- Map Markers -->
                        <div id="mapMarkers" class="w-full h-full relative">
                            <!-- Marker 1 -->
                            <div class="map-marker" style="top: 35%; left: 25%;" data-property-id="1" onclick="highlightProperty(1)">
                                <div class="marker-tooltip">
                                    <div class="text-xs font-semibold text-gray-900">Alove Avenue</div>
                                    <div class="text-xs text-teal-700 font-bold">$450,000</div>
                                </div>
                                <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg border-4 border-white">
                                    <i class="fas fa-home text-sm"></i>
                                </div>
                            </div>

                            <!-- Marker 2 -->
                            <div class="map-marker" style="top: 45%; left: 60%;" data-property-id="2" onclick="highlightProperty(2)">
                                <div class="marker-tooltip">
                                    <div class="text-xs font-semibold text-gray-900">725 NE 168th St</div>
                                    <div class="text-xs text-teal-700 font-bold">$1,500/mo</div>
                                </div>
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg border-4 border-white">
                                    <i class="fas fa-building text-sm"></i>
                                </div>
                            </div>

                            <!-- Marker 3 -->
                            <div class="map-marker" style="top: 60%; left: 40%;" data-property-id="3" onclick="highlightProperty(3)">
                                <div class="marker-tooltip">
                                    <div class="text-xs font-semibold text-gray-900">261 SW 8th St</div>
                                    <div class="text-xs text-teal-700 font-bold">$220,000</div>
                                </div>
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg border-4 border-white">
                                    <i class="fas fa-building text-sm"></i>
                                </div>
                            </div>

                            <!-- Marker 4 -->
                            <div class="map-marker" style="top: 50%; left: 75%;" data-property-id="4" onclick="highlightProperty(4)">
                                <div class="marker-tooltip">
                                    <div class="text-xs font-semibold text-gray-900">1551 West Ave</div>
                                    <div class="text-xs text-teal-700 font-bold">$459,000</div>
                                </div>
                                <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg border-4 border-white">
                                    <i class="fas fa-home text-sm"></i>
                                </div>
                            </div>

                            <!-- Marker 5 -->
                            <div class="map-marker" style="top: 70%; left: 30%;" data-property-id="5" onclick="highlightProperty(5)">
                                <div class="marker-tooltip">
                                    <div class="text-xs font-semibold text-gray-900">8230 W Flagler St</div>
                                    <div class="text-xs text-teal-700 font-bold">$5,000/mo</div>
                                </div>
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg border-4 border-white">
                                    <i class="fas fa-briefcase text-sm"></i>
                                </div>
                            </div>

                            <!-- Marker 6 -->
                            <div class="map-marker" style="top: 25%; left: 80%;" data-property-id="6" onclick="highlightProperty(6)">
                                <div class="marker-tooltip">
                                    <div class="text-xs font-semibold text-gray-900">924 Marseille Dr</div>
                                    <div class="text-xs text-teal-700 font-bold">$4,395,000</div>
                                </div>
                                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg border-4 border-white">
                                    <i class="fas fa-building text-sm"></i>
                                </div>
                            </div>

                            <!-- Marker 7 -->
                            <div class="map-marker" style="top: 40%; left: 50%;" data-property-id="7" onclick="highlightProperty(7)">
                                <div class="marker-tooltip">
                                    <div class="text-xs font-semibold text-gray-900">Luxury Penthouse</div>
                                    <div class="text-xs text-teal-700 font-bold">$2,800,000</div>
                                </div>
                                <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold shadow-lg border-4 border-white">
                                    <i class="fas fa-crown text-sm"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16">
        <div class="container mx-auto px-4 lg:px-6 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p class="text-gray-400">Copyright © 2025 <span class="text-teal-400 font-semibold">Villea</span>. Designed By Pixelaxis</p>
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-teal-400 transition">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-teal-400 transition">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-teal-400 transition">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-teal-400 transition">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('active');
        }

        // Dropdown Toggle
        let activeDropdown = null;

        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const allDropdowns = document.querySelectorAll('.dropdown-content');
            
            // Close all other dropdowns
            allDropdowns.forEach(dd => {
                if (dd.id !== dropdownId) {
                    dd.classList.add('hidden');
                }
            });
            
            // Toggle current dropdown
            if (dropdown.classList.contains('hidden')) {
                dropdown.classList.remove('hidden');
                activeDropdown = dropdown;
            } else {
                dropdown.classList.add('hidden');
                activeDropdown = null;
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('.dropdown-content');
            const filterButtons = document.querySelectorAll('.filter-chip');
            
            let clickedOnButton = false;
            filterButtons.forEach(button => {
                if (button.contains(event.target)) {
                    clickedOnButton = true;
                }
            });
            
            if (!clickedOnButton) {
                let clickedInsideDropdown = false;
                dropdowns.forEach(dropdown => {
                    if (dropdown.contains(event.target)) {
                        clickedInsideDropdown = true;
                    }
                });
                
                if (!clickedInsideDropdown) {
                    dropdowns.forEach(dropdown => {
                        dropdown.classList.add('hidden');
                    });
                    activeDropdown = null;
                }
            }
        });

        // Change Layout (Grid/Column)
        function changeLayout(layout) {
            const gridBtn = document.getElementById('gridBtn');
            const columnBtn = document.getElementById('columnBtn');
            const propertyGrid = document.getElementById('propertyGrid');

            if (layout === 'grid') {
                gridBtn.classList.add('active');
                columnBtn.classList.remove('active');
                propertyGrid.className = 'grid grid-cols-1 md:grid-cols-2 gap-6';
            } else {
                columnBtn.classList.add('active');
                gridBtn.classList.remove('active');
                propertyGrid.className = 'grid grid-cols-1 gap-6';
            }
        }

        // Toggle View (List/Map)
        function toggleView(view) {
            // This would typically change the entire layout
            console.log('Switching to', view, 'view');
        }

        // Highlight Property on Map Click
        function highlightProperty(propertyId) {
            // Remove previous highlights
            document.querySelectorAll('.property-card').forEach(card => {
                card.classList.remove('ring-4', 'ring-teal-500');
            });
            
            document.querySelectorAll('.map-marker').forEach(marker => {
                marker.classList.remove('active');
            });

            // Add highlight to selected property
            const propertyCard = document.querySelector(`.property-card[data-property-id="${propertyId}"]`);
            const mapMarker = document.querySelector(`.map-marker[data-property-id="${propertyId}"]`);
            
            if (propertyCard) {
                propertyCard.classList.add('ring-4', 'ring-teal-500');
                propertyCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            if (mapMarker) {
                mapMarker.classList.add('active');
            }
        }

        // Property Card Hover - Highlight Map Marker
        document.querySelectorAll('.property-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                const propertyId = this.getAttribute('data-property-id');
                const marker = document.querySelector(`.map-marker[data-property-id="${propertyId}"]`);
                if (marker) {
                    marker.classList.add('active');
                }
            });
            
            card.addEventListener('mouseleave', function() {
                const propertyId = this.getAttribute('data-property-id');
                const marker = document.querySelector(`.map-marker[data-property-id="${propertyId}"]`);
                if (marker && !this.classList.contains('ring-4')) {
                    marker.classList.remove('active');
                }
            });
        });

        // Search Functionality
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.property-card').forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Update results count
            const visibleCards = document.querySelectorAll('.property-card[style="display: block;"]').length;
            const allCards = document.querySelectorAll('.property-card').length;
            document.getElementById('resultsCount').textContent = searchTerm ? visibleCards : allCards;
        });

        // Heart/Favorite Toggle
        document.querySelectorAll('.fa-heart').forEach(heart => {
            heart.parentElement.addEventListener('click', function(e) {
                e.preventDefault();
                heart.classList.toggle('far');
                heart.classList.toggle('fas');
                heart.classList.toggle('text-red-500');
            });
        });

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Loading Animation on Page Load
        window.addEventListener('load', function() {
            document.body.classList.add('page-transition');
        });

        // Intersection Observer for Property Cards Animation
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '0';
                    entry.target.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        entry.target.style.transition = 'all 0.6s ease';
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, 100);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.property-card').forEach(card => {
            observer.observe(card);
        });
    </script>
</div>

<?php get_footer(); ?>

