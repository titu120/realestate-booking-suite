<?php get_header(); ?>








<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">

<div class="rbs-archive">

    <!-- Advanced Search Bar -->
    <div class="search-bar">
        <div class="container">
            <div class="search-container">
                <!-- Search Input -->
                <div class="search-input-container">
                    <i class="fas fa-search search-icon"></i>
                    <input
                        type="text"
                        placeholder="Address, City, ZIP..."
                        class="search-input"
                        id="searchInput"
                    >
                </div>

                <!-- Filter Buttons -->
                <div class="filter-buttons">
                    <button onclick="toggleDropdown('priceDropdown')" class="filter-chip">
                        <span>Price</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <button onclick="toggleDropdown('typeDropdown')" class="filter-chip">
                        <span>Type</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <button onclick="toggleDropdown('bedroomsDropdown')" class="filter-chip">
                        <span>Bedrooms</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <button onclick="toggleDropdown('bathroomsDropdown')" class="filter-chip">
                        <span>Bathrooms</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <button onclick="toggleDropdown('moreFiltersDropdown')" class="filter-chip">
                        <span>More filters</span>
                        <i class="fas fa-sliders-h"></i>
                    </button>

                    <button class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>

            <!-- Dropdown Panels Container -->
            <div class="dropdowns-container">
                <!-- Price Dropdown -->
                <div id="priceDropdown" class="dropdown-content">
                    <div class="dropdown-grid">
                        <div>
                            <label class="dropdown-label">Min Price</label>
                            <input type="text" placeholder="$ Min Price" class="dropdown-input">
                        </div>
                        <div>
                            <label class="dropdown-label">Max Price</label>
                            <input type="text" placeholder="$ Max Price" class="dropdown-input">
                        </div>
                    </div>
                </div>

                <!-- Type Dropdown -->
                <div id="typeDropdown" class="dropdown-content">
                    <div class="checkbox-grid">
                        <label class="checkbox-item">
                            <input type="checkbox">
                            <span>House</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox">
                            <span>Apartment</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox">
                            <span>Condo</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox">
                            <span>Office</span>
                        </label>
                    </div>
                </div>

                <!-- Bedrooms Dropdown -->
                <div id="bedroomsDropdown" class="dropdown-content">
                    <div class="filter-options">
                        <button class="filter-option">Any</button>
                        <button class="filter-option">1+</button>
                        <button class="filter-option">2+</button>
                        <button class="filter-option">3+</button>
                        <button class="filter-option">4+</button>
                        <button class="filter-option">5+</button>
                    </div>
                </div>

                <!-- Bathrooms Dropdown -->
                <div id="bathroomsDropdown" class="dropdown-content">
                    <div class="filter-options">
                        <button class="filter-option">Any</button>
                        <button class="filter-option">1+</button>
                        <button class="filter-option">2+</button>
                        <button class="filter-option">3+</button>
                        <button class="filter-option">4+</button>
                    </div>
                </div>

                <!-- More Filters Dropdown -->
                <div id="moreFiltersDropdown" class="dropdown-content">
                    <div class="dropdown-grid">
                        <div>
                            <label class="dropdown-label">Square Feet</label>
                            <div class="flex" style="gap: 8px;">
                                <input type="number" placeholder="Min" class="dropdown-input">
                                <input type="number" placeholder="Max" class="dropdown-input">
                            </div>
                        </div>
                        <div>
                            <label class="dropdown-label">Year Built</label>
                            <select class="dropdown-input">
                                <option>Any</option>
                                <option>2020+</option>
                                <option>2010+</option>
                                <option>2000+</option>
                                <option>1990+</option>
                            </select>
                        </div>
                        <div>
                            <label class="dropdown-label">Status</label>
                            <select class="dropdown-input">
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
    <div class="container main-content">
        <!-- Control Bar -->
        <div class="control-bar">
            <!-- Left Side -->
            <div class="view-controls">
                <button onclick="toggleView('list')" class="view-btn">
                    <i class="fas fa-list"></i>
                    <span>List View</span>
                </button>
                <div class="view-divider">|</div>
                <button onclick="toggleView('map')" class="view-btn active">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Map View</span>
                </button>
                <div class="results-count">
                    <span id="resultsCount">7</span> results
                </div>
            </div>

            <!-- Right Side -->
            <div class="sort-controls">
                <span class="sort-label">Sort by:</span>
                <select class="sort-select">
                    <option>Newest</option>
                    <option>Price: Low to High</option>
                    <option>Price: High to Low</option>
                    <option>Most Popular</option>
                    <option>Recently Updated</option>
                </select>

                <div class="layout-toggle">
                    <button onclick="changeLayout('grid')" id="gridBtn" class="layout-btn active">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button onclick="changeLayout('column')" id="columnBtn" class="layout-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <button class="filter-toggle" onclick="toggleMap()" id="mapToggleBtn">
                    <i class="fas fa-map-marked-alt"></i>
                </button>
            </div>
        </div>

        <!-- Map and Listings Layout -->
        <div class="listings-container">
            <!-- Property Listings -->
            <div class="properties-list" id="propertiesContainer">
                <div id="propertyGrid" class="property-grid">
                    <!-- Property Card 1 -->
                    <div class="property-card" data-property-id="1">
                        <div class="property-image">
                            <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800" alt="Property">
                            <div class="gradient-overlay"></div>
                            <div class="property-badge badge-featured">Featured</div>
                            <button class="favorite-btn">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="property-info-overlay">
                                <h3 class="property-title">Alove Avenue</h3>
                                <p class="property-location">Alove Avenue</p>
                            </div>
                        </div>
                        <div class="property-details">
                            <div class="property-price-container">
                                <span class="property-price">$450,000</span>
                                <span class="property-status">For sale</span>
                            </div>
                            <div class="property-features">
                                <div class="property-feature">
                                    <i class="fas fa-bed"></i>
                                    <span>2 beds</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-bath"></i>
                                    <span>2 baths</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>630 sq ft</span>
                                </div>
                            </div>
                            <div class="property-footer">
                                <span class="property-type">Apartment</span>
                                <button class="view-details-btn">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Card 2 -->
                    <div class="property-card" data-property-id="2">
                        <div class="property-image">
                            <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800" alt="Property">
                            <div class="gradient-overlay"></div>
                            <div class="property-badge badge-new">Just listed</div>
                            <button class="favorite-btn">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="property-info-overlay">
                                <h3 class="property-title">725 NE 168th St</h3>
                                <p class="property-location">Miami, FL 33162</p>
                            </div>
                        </div>
                        <div class="property-details">
                            <div class="property-price-container">
                                <span class="property-price">$1,500</span>
                                <span class="property-status">For rent</span>
                            </div>
                            <div class="property-features">
                                <div class="property-feature">
                                    <i class="fas fa-bed"></i>
                                    <span>3 beds</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-bath"></i>
                                    <span>2 baths</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>850 sq ft</span>
                                </div>
                            </div>
                            <div class="property-footer">
                                <span class="property-type">Apartment</span>
                                <button class="view-details-btn">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Card 3 -->
                    <div class="property-card" data-property-id="3">
                        <div class="property-image">
                            <img src="https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800" alt="Property">
                            <div class="gradient-overlay"></div>
                            <div class="property-badge badge-new">Just listed</div>
                            <button class="favorite-btn">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="property-info-overlay">
                                <h3 class="property-title">261 SW 8th St</h3>
                                <p class="property-location">Miami, FL 33130</p>
                            </div>
                        </div>
                        <div class="property-details">
                            <div class="property-price-container">
                                <span class="property-price">$220,000</span>
                                <span class="property-status">For sale</span>
                            </div>
                            <div class="property-features">
                                <div class="property-feature">
                                    <i class="fas fa-bed"></i>
                                    <span>5 beds</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-bath"></i>
                                    <span>2 baths</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>700 sq ft</span>
                                </div>
                            </div>
                            <div class="property-footer">
                                <span class="property-type">Condo</span>
                                <button class="view-details-btn">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Card 4 -->
                    <div class="property-card" data-property-id="4">
                        <div class="property-image">
                            <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800" alt="Property">
                            <div class="gradient-overlay"></div>
                            <div class="property-badge badge-featured">Featured</div>
                            <button class="favorite-btn">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="property-info-overlay">
                                <h3 class="property-title">1551 West Ave</h3>
                                <p class="property-location">Miami Beach, FL 33139</p>
                            </div>
                        </div>
                        <div class="property-details">
                            <div class="property-price-container">
                                <span class="property-price">$459,000</span>
                                <span class="property-status">For sale</span>
                            </div>
                            <div class="property-features">
                                <div class="property-feature">
                                    <i class="fas fa-bed"></i>
                                    <span>3 beds</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-bath"></i>
                                    <span>2 baths</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>679 sq ft</span>
                                </div>
                            </div>
                            <div class="property-footer">
                                <span class="property-type">House</span>
                                <button class="view-details-btn">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Card 5 -->
                    <div class="property-card" data-property-id="5">
                        <div class="property-image">
                            <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800" alt="Property">
                            <div class="gradient-overlay"></div>
                            <div class="property-badge badge-new">Just listed</div>
                            <button class="favorite-btn">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="property-info-overlay">
                                <h3 class="property-title">8230 W Flagler St</h3>
                                <p class="property-location">Miami, FL 33144</p>
                            </div>
                        </div>
                        <div class="property-details">
                            <div class="property-price-container">
                                <span class="property-price">$5,000</span>
                                <span class="property-status">For rent</span>
                            </div>
                            <div class="property-features">
                                <div class="property-feature">
                                    <i class="fas fa-bed"></i>
                                    <span>3 beds</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-bath"></i>
                                    <span>3 baths</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>350 sq ft</span>
                                </div>
                            </div>
                            <div class="property-footer">
                                <span class="property-type">Office</span>
                                <button class="view-details-btn">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Card 6 -->
                    <div class="property-card" data-property-id="6">
                        <div class="property-image">
                            <img src="https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?w=800" alt="Property">
                            <div class="gradient-overlay"></div>
                            <div class="property-badge badge-new">Just listed</div>
                            <button class="favorite-btn">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="property-info-overlay">
                                <h3 class="property-title">924 Marseille Dr</h3>
                                <p class="property-location">Miami Beach, FL 33141</p>
                            </div>
                        </div>
                        <div class="property-details">
                            <div class="property-price-container">
                                <span class="property-price">$4,395,000</span>
                                <span class="property-status">For sale</span>
                            </div>
                            <div class="property-features">
                                <div class="property-feature">
                                    <i class="fas fa-bed"></i>
                                    <span>5 beds</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-bath"></i>
                                    <span>3 baths</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>1,200 sq ft</span>
                                </div>
                            </div>
                            <div class="property-footer">
                                <span class="property-type">Apartment</span>
                                <button class="view-details-btn">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Property Card 7 -->
                    <div class="property-card" data-property-id="7">
                        <div class="property-image">
                            <img src="https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800" alt="Property">
                            <div class="gradient-overlay"></div>
                            <div class="property-badge badge-featured">Featured</div>
                            <button class="favorite-btn">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="property-info-overlay">
                                <h3 class="property-title">Luxury Penthouse</h3>
                                <p class="property-location">Downtown Miami</p>
                            </div>
                        </div>
                        <div class="property-details">
                            <div class="property-price-container">
                                <span class="property-price">$2,800,000</span>
                                <span class="property-status">For sale</span>
                            </div>
                            <div class="property-features">
                                <div class="property-feature">
                                    <i class="fas fa-bed"></i>
                                    <span>4 beds</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-bath"></i>
                                    <span>4 baths</span>
                                </div>
                                <div class="property-feature">
                                    <i class="fas fa-ruler-combined"></i>
                                    <span>2,500 sq ft</span>
                                </div>
                            </div>
                            <div class="property-footer">
                                <span class="property-type">Penthouse</span>
                                <button class="view-details-btn">
                                    View Details <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive Map -->
            <div class="map-section">
                <div class="map-container">
                    <div class="map-bg">
                        <!-- Map Controls -->
                        <div class="map-controls">
                            <button class="map-control">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="map-control">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button class="map-control">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>

                        <!-- Map Legend -->
                        <div class="map-legend">
                            <h4 class="legend-title">Legend</h4>
                            <div class="legend-items">
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #f97316;"></div>
                                    <span class="legend-label">Featured</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #10b981;"></div>
                                    <span class="legend-label">Just Listed</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #0f766e;"></div>
                                    <span class="legend-label">Standard</span>
                                </div>
                            </div>
                        </div>

                        <!-- Map Markers -->
                        <div id="mapMarkers" class="map-markers">
                            <!-- Marker 1 -->
                            <div class="map-marker" style="top: 35%; left: 25%;" data-property-id="1" onclick="highlightProperty(1)">
                                <div class="marker-tooltip">
                                    <div class="tooltip-title">Alove Avenue</div>
                                    <div class="tooltip-price">$450,000</div>
                                </div>
                                <div class="marker-icon" style="background-color: #f97316;">
                                    <i class="fas fa-home"></i>
                                </div>
                            </div>

                            <!-- Marker 2 -->
                            <div class="map-marker" style="top: 45%; left: 60%;" data-property-id="2" onclick="highlightProperty(2)">
                                <div class="marker-tooltip">
                                    <div class="tooltip-title">725 NE 168th St</div>
                                    <div class="tooltip-price">$1,500/mo</div>
                                </div>
                                <div class="marker-icon" style="background-color: #10b981;">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>

                            <!-- Marker 3 -->
                            <div class="map-marker" style="top: 60%; left: 40%;" data-property-id="3" onclick="highlightProperty(3)">
                                <div class="marker-tooltip">
                                    <div class="tooltip-title">261 SW 8th St</div>
                                    <div class="tooltip-price">$220,000</div>
                                </div>
                                <div class="marker-icon" style="background-color: #10b981;">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>

                            <!-- Marker 4 -->
                            <div class="map-marker" style="top: 50%; left: 75%;" data-property-id="4" onclick="highlightProperty(4)">
                                <div class="marker-tooltip">
                                    <div class="tooltip-title">1551 West Ave</div>
                                    <div class="tooltip-price">$459,000</div>
                                </div>
                                <div class="marker-icon" style="background-color: #f97316;">
                                    <i class="fas fa-home"></i>
                                </div>
                            </div>

                            <!-- Marker 5 -->
                            <div class="map-marker" style="top: 70%; left: 30%;" data-property-id="5" onclick="highlightProperty(5)">
                                <div class="marker-tooltip">
                                    <div class="tooltip-title">8230 W Flagler St</div>
                                    <div class="tooltip-price">$5,000/mo</div>
                                </div>
                                <div class="marker-icon" style="background-color: #10b981;">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                            </div>

                            <!-- Marker 6 -->
                            <div class="map-marker" style="top: 25%; left: 80%;" data-property-id="6" onclick="highlightProperty(6)">
                                <div class="marker-tooltip">
                                    <div class="tooltip-title">924 Marseille Dr</div>
                                    <div class="tooltip-price">$4,395,000</div>
                                </div>
                                <div class="marker-icon" style="background-color: #10b981;">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>

                            <!-- Marker 7 -->
                            <div class="map-marker" style="top: 40%; left: 50%;" data-property-id="7" onclick="highlightProperty(7)">
                                <div class="marker-tooltip">
                                    <div class="tooltip-title">Luxury Penthouse</div>
                                    <div class="tooltip-price">$2,800,000</div>
                                </div>
                                <div class="marker-icon" style="background-color: #f97316;">
                                    <i class="fas fa-crown"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



</div>

<script>
// Map Toggle Functionality
function toggleMap() {
    const mapSection = document.querySelector('.map-section');
    const listingsContainer = document.querySelector('.listings-container');
    const mapToggleBtn = document.getElementById('mapToggleBtn');
    const mapIcon = mapToggleBtn.querySelector('i');
    const propertyGrid = document.getElementById('propertyGrid');
    const gridBtn = document.getElementById('gridBtn');
    
    if (mapSection.classList.contains('map-visible')) {
        // Hide map (default state)
        mapSection.classList.remove('map-visible');
        mapSection.classList.add('map-hidden');
        listingsContainer.classList.remove('map-visible');
        mapIcon.className = 'fas fa-map-marked-alt';
        mapToggleBtn.title = 'Show Map';
        
        // Adjust grid layout if grid view is active
        if (gridBtn.classList.contains('active')) {
            propertyGrid.style.setProperty('grid-template-columns', 'repeat(4, 1fr)', 'important');
        }
    } else {
        // Show map
        mapSection.classList.remove('map-hidden');
        mapSection.classList.add('map-visible');
        listingsContainer.classList.add('map-visible');
        mapIcon.className = 'fas fa-eye-slash';
        mapToggleBtn.title = 'Hide Map';
        
        // Adjust grid layout if grid view is active
        if (gridBtn.classList.contains('active')) {
            propertyGrid.style.setProperty('grid-template-columns', 'repeat(2, 1fr)', 'important');
        }
    }
}

// Dropdown toggle functionality
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const allDropdowns = document.querySelectorAll('.dropdown-content');
    
    // Close all other dropdowns
    allDropdowns.forEach(dd => {
        if (dd.id !== dropdownId) {
            dd.style.display = 'none';
        }
    });
    
    // Toggle current dropdown
    if (dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
    } else {
        dropdown.style.display = 'block';
    }
}

// View toggle functionality
function toggleView(viewType) {
    const listBtn = document.querySelector('[onclick="toggleView(\'list\')"]');
    const mapBtn = document.querySelector('[onclick="toggleView(\'map\')"]');
    
    // Remove active class from all buttons
    listBtn.classList.remove('active');
    mapBtn.classList.remove('active');
    
    // Add active class to clicked button
    if (viewType === 'list') {
        listBtn.classList.add('active');
    } else {
        mapBtn.classList.add('active');
    }
}

// Layout change functionality
function changeLayout(layoutType) {
    const gridBtn = document.getElementById('gridBtn');
    const columnBtn = document.getElementById('columnBtn');
    const propertyGrid = document.getElementById('propertyGrid');
    
    // Remove active class from all buttons
    gridBtn.classList.remove('active');
    columnBtn.classList.remove('active');
    
    if (layoutType === 'grid') {
        gridBtn.classList.add('active');
        // Check if map is visible to determine grid columns
        const mapSection = document.querySelector('.map-section');
        if (mapSection.classList.contains('map-visible')) {
            // With map - 2 columns
            propertyGrid.style.setProperty('grid-template-columns', 'repeat(2, 1fr)', 'important');
        } else {
            // Full width - 4 columns
            propertyGrid.style.setProperty('grid-template-columns', 'repeat(4, 1fr)', 'important');
        }
    } else {
        columnBtn.classList.add('active');
        propertyGrid.style.gridTemplateColumns = '1fr';
    }
}

// Property highlighting functionality
function highlightProperty(propertyId) {
    // Remove active class from all markers and cards
    document.querySelectorAll('.map-marker').forEach(marker => {
        marker.classList.remove('active');
    });
    document.querySelectorAll('.property-card').forEach(card => {
        card.classList.remove('active');
    });
    
    // Add active class to clicked marker and corresponding card
    const marker = document.querySelector(`[data-property-id="${propertyId}"]`);
    const card = document.querySelector(`.property-card[data-property-id="${propertyId}"]`);
    
    if (marker) marker.classList.add('active');
    if (card) card.classList.add('active');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('.dropdown-content');
    const filterButtons = document.querySelectorAll('.filter-chip');
    
    let clickedInsideDropdown = false;
    let clickedInsideFilterButton = false;
    
    // Check if click was inside a dropdown
    dropdowns.forEach(dropdown => {
        if (dropdown.contains(event.target)) {
            clickedInsideDropdown = true;
        }
    });
    
    // Check if click was inside a filter button
    filterButtons.forEach(button => {
        if (button.contains(event.target)) {
            clickedInsideFilterButton = true;
        }
    });
    
    // Close all dropdowns if click was outside
    if (!clickedInsideDropdown && !clickedInsideFilterButton) {
        dropdowns.forEach(dropdown => {
            dropdown.style.display = 'none';
        });
    }
});

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Set initial map toggle button title and state
    const mapToggleBtn = document.getElementById('mapToggleBtn');
    const mapSection = document.querySelector('.map-section');
    const propertyGrid = document.getElementById('propertyGrid');
    const gridBtn = document.getElementById('gridBtn');
    
    if (mapToggleBtn) {
        mapToggleBtn.title = 'Show Map';
    }
    
    // Set initial map state to hidden
    if (mapSection) {
        mapSection.classList.add('map-hidden');
    }
    
    // Set initial grid layout (4 columns for full width by default)
    if (propertyGrid) {
        propertyGrid.style.setProperty('grid-template-columns', 'repeat(4, 1fr)', 'important');
    }
});
</script>

<?php get_footer(); ?>
