<?php get_header(); ?>






<style>
    /* Base Styles */
    .rbs-archive * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Inter', sans-serif;
    }
    
    .rbs-archive body {
        background-color: #f9fafb;
        color: #374151;
        overflow-x: hidden;
    }

    .rbs-archive .container {
        width: 100%;
        max-width: 1540px;
        margin: 0 auto;
        padding: 0 16px;
    }

    /* Header Styles */
    .rbs-archive header {
        background-color: white;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 0;
        z-index: 50;
        border-bottom: 1px solid #f3f4f6;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .rbs-archive .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 80px;
    }

    .rbs-archive .logo {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .rbs-archive .logo-icon {
        width: 40px;
        height: 40px;
        background-color: #0f766e;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .rbs-archive .logo-icon i {
        color: white;
        font-size: 20px;
    }

    .rbs-archive .logo-text {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
    }

    .rbs-archive .desktop-nav {
        display: none;
        align-items: center;
        gap: 32px;
    }

    .rbs-archive .nav-link {
        color: #374151;
        font-weight: 500;
        transition: color 0.3s;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .rbs-archive .nav-link:hover {
        color: #0f766e;
    }

    .rbs-archive .nav-link i {
        font-size: 12px;
    }

    .rbs-archive .header-actions {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .rbs-archive .header-btn {
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s;
        cursor: pointer;
    }

    .rbs-archive .header-btn-outline {
        border: 2px solid #d1d5db;
        background: transparent;
        color: #374151;
    }

    .rbs-archive .header-btn-outline:hover {
        border-color: #0f766e;
        color: #0f766e;
    }

    .rbs-archive .header-btn-filled {
        background-color: #0f766e;
        color: white;
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .rbs-archive .header-btn-filled:hover {
        background-color: #0d5c54;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    .rbs-archive .mobile-menu-btn {
        display: block;
        color: #374151;
        font-size: 24px;
        cursor: pointer;
    }

    /* Mobile Menu */
    .rbs-archive .mobile-menu {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: white;
        z-index: 50;
        padding: 24px;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .rbs-archive .mobile-menu.active {
        transform: translateX(0);
    }

    .rbs-archive .mobile-menu-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
    }

    .rbs-archive .mobile-menu-title {
        font-size: 24px;
        font-weight: 700;
    }

    .rbs-archive .mobile-menu-close {
        font-size: 24px;
        cursor: pointer;
    }

    .rbs-archive .mobile-nav {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .rbs-archive .mobile-nav-link {
        font-size: 18px;
        font-weight: 500;
        color: #374151;
    }

    .rbs-archive .mobile-nav-link:hover {
        color: #0f766e;
    }

    .rbs-archive .mobile-menu-btn {
        width: 100%;
        margin-top: 16px;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
    }

    /* Search Bar */
    .rbs-archive .search-bar {
        background-color: white;
        border-bottom: 1px solid #e5e7eb;
        padding: 24px 0;
    }

    .rbs-archive .search-container {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .rbs-archive .search-input-container {
        flex: 1;
        position: relative;
    }

    .rbs-archive .search-input {
        width: 100%;
        padding: 14px 48px;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s;
    }

    .rbs-archive .search-input:focus {
        outline: none;
        border-color: #0f766e;
        box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.1);
    }

    .rbs-archive .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }

    .rbs-archive .filter-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        position: relative;
        align-items: flex-start;
    }

    .rbs-archive .filter-chip {
        padding: 12px 20px;
        background-color: white;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-weight: 500;
        color: #374151;
        transition: all 0.3s;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: auto;
        flex-shrink: 0;
        white-space: nowrap;
        min-width: auto;
        max-width: none;
    }

    .rbs-archive .filter-chip:hover {
        border-color: #0f766e;
        color: #0f766e;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .rbs-archive .search-btn {
        padding: 12px 20px;
        background-color: #0f766e;
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .rbs-archive .search-btn:hover {
        background-color: #0d5c54;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    /* Dropdowns */
    .rbs-archive .dropdowns-container {
        position: relative;
        margin-top: 16px;
    }

    .rbs-archive .dropdown-content {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 50;
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        animation: slideDown 0.3s ease;
        min-width: 200px;
        width: auto;
    }

    @keyframes rbs-archive-slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .rbs-archive .dropdown-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .rbs-archive .dropdown-label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 8px;
    }

    .rbs-archive .dropdown-input {
        width: 100%;
        padding: 10px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
    }

    .rbs-archive .dropdown-input:focus {
        outline: none;
        border-color: #0f766e;
        box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.1);
    }

    .rbs-archive .checkbox-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .rbs-archive .checkbox-item {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: color 0.3s;
    }

    .rbs-archive .checkbox-item:hover {
        color: #0f766e;
    }

    .rbs-archive .checkbox-item input {
        width: 16px;
        height: 16px;
        accent-color: #0f766e;
    }

    .rbs-archive .filter-options {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .rbs-archive .filter-option {
        padding: 10px 24px;
        border: 2px solid #d1d5db;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }

    .rbs-archive .filter-option:hover {
        background-color: #0f766e;
        color: white;
        border-color: #0f766e;
    }

    /* Main Content */
    .rbs-archive .main-content {
        padding: 24px 0;
    }

    .rbs-archive .control-bar {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 24px;
    }

    .rbs-archive .view-controls {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .rbs-archive .view-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6b7280;
        transition: color 0.3s;
        cursor: pointer;
        border: unset;
    }
    .rbs-archive .view-btn:hover {
        background-color: unset;
    }

    .rbs-archive .view-btn.active {
        color: #0f766e;
        font-weight: 500;
    }

    .rbs-archive .view-btn i {
        font-size: 20px;
    }

    .rbs-archive .view-divider {
        color: #d1d5db;
    }

    .rbs-archive .results-count {
        padding: 8px 16px;
        background-color: #ecfdf5;
        color: #0f766e;
        border-radius: 8px;
        font-weight: 500;
    }

    .rbs-archive .sort-controls {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .rbs-archive .sort-label {
        color: #6b7280;
        font-weight: 500;
        white-space: nowrap;
    }

    .rbs-archive .sort-select {
        padding: 8px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-weight: 500;
    }

    .rbs-archive .sort-select:focus {
        outline: none;
        border-color: #0f766e;
        box-shadow: 0 0 0 2px rgba(15, 118, 110, 0.1);
    }

    .rbs-archive .layout-toggle {
        display: flex;
        background-color: #f3f4f6;
        border-radius: 8px;
        padding: 4px;
    }

    .rbs-archive .layout-btn {
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .rbs-archive .layout-btn.active {
        background-color: #0f766e;
        color: white;
    }

    .rbs-archive .filter-toggle {
        padding: 8px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .rbs-archive .filter-toggle:hover {
        background-color: #f9fafb;
    }

    /* Property Listings */
    .rbs-archive .listings-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }

    .rbs-archive .properties-list {
        overflow-y: auto;
        max-height: calc(100vh - 300px);
    }

    .rbs-archive .properties-list::-webkit-scrollbar {
        width: 6px;
    }

    .rbs-archive .properties-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .rbs-archive .properties-list::-webkit-scrollbar-thumb {
        background: #0f766e;
        border-radius: 10px;
    }

    .rbs-archive .properties-list::-webkit-scrollbar-thumb:hover {
        background: #0d5c54;
    }

    .rbs-archive .property-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }

    .rbs-archive .property-card {
        background-color: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .rbs-archive .property-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .rbs-archive .property-image {
        position: relative;
        overflow: hidden;
        height: 224px;
    }

    .rbs-archive .property-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .rbs-archive .property-card:hover .property-image img {
        transform: scale(1.1);
    }

    .rbs-archive .gradient-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, rgba(0,0,0,0) 0%, rgba(0,0,0,0.7) 100%);
    }

    .rbs-archive .property-badge {
        position: absolute;
        top: 16px;
        left: 16px;
        padding: 6px 16px;
        color: white;
        border-radius: 9999px;
        font-size: 14px;
        font-weight: 600;
        animation: rbs-archive-pulse 2s infinite;
    }

    @keyframes rbs-archive-pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
        }
    }

    .rbs-archive .badge-featured {
        background-color: #f97316;
    }

    .rbs-archive .badge-new {
        background-color: #10b981;
    }

    .rbs-archive .property-image .favorite-btn {
        color: unset !important;
    }

    .rbs-archive .favorite-btn {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 40px;
        height: 40px;
        background-color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: unset;
    }

    .rbs-archive .favorite-btn:hover {
        background-color: #0f766e;
        color: white;
    }

    .rbs-archive .property-info-overlay {
        position: absolute;
        bottom: 16px;
        left: 16px;
        right: 16px;
    }

    .rbs-archive .property-title {
        color: white;
        font-weight: 700;
        font-size: 20px;
        margin-bottom: 4px;
    }

    .rbs-archive .property-location {
        color: rgba(255, 255, 255, 0.9);
        font-size: 14px;
    }

    .rbs-archive .property-details {
        padding: 20px;
    }

    .rbs-archive .property-price-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .rbs-archive .property-price {
        font-size: 24px;
        font-weight: 700;
        color: #0f766e;
    }

    .rbs-archive .property-status {
        font-size: 14px;
        color: #6b7280;
        background-color: #f3f4f6;
        padding: 4px 12px;
        border-radius: 9999px;
    }

    .rbs-archive .property-features {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 16px;
    }

    .rbs-archive .property-feature {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .rbs-archive  button{
        border: unset;
        color: unset;
    }

    .rbs-archive .property-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 16px;
        border-top: 1px solid #f3f4f6;
    }

    .rbs-archive .property-type {
        font-size: 14px;
        color: #6b7280;
    }

    .rbs-archive .view-details-btn {
        color: #0f766e;
        font-weight: 500;
        cursor: pointer;
        transition: color 0.3s;
        border: unset;
    }
    .rbs-archive .view-details-btn:hover {
        background-color: unset;
    }

    .rbs-archive .view-details-btn:hover {
        color: #0d5c54;
    }

    /* Map Section */
    .rbs-archive .map-section {
        position: relative;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        height: calc(100vh - 300px);
        position: sticky;
        top: 100px;
    }

    .rbs-archive .map-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
        width: 100%;
        height: 100%;
    }

    .rbs-archive .map-bg {
        background-image: 
            radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 40% 20%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
        width: 100%;
        height: 100%;
        position: relative;
    }

    .rbs-archive .map-controls {
        position: absolute;
        top: 16px;
        right: 16px;
        z-index: 20;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .rbs-archive .map-control {
        width: 40px;
        height: 40px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .rbs-archive .map-control:hover {
        background-color: #f9fafb;
    }

    .rbs-archive .map-legend {
        position: absolute;
        bottom: 16px;
        left: 16px;
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 16px;
        z-index: 20;
    }

    .rbs-archive .legend-title {
        font-weight: 600;
        color: #111827;
        margin-bottom: 12px;
    }

    .rbs-archive .legend-items {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .rbs-archive .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .rbs-archive .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .rbs-archive .legend-label {
        font-size: 14px;
        color: #6b7280;
    }

    .rbs-archive .map-markers {
        width: 100%;
        height: 100%;
        position: relative;
    }

    .rbs-archive .map-marker {
        position: absolute;
        transform: translate(-50%, -100%);
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 10;
    }

    .rbs-archive .map-marker:hover {
        z-index: 20;
        transform: translate(-50%, -100%) scale(1.15);
    }

    .rbs-archive .map-marker.active {
        z-index: 30;
        transform: translate(-50%, -100%) scale(1.2);
    }

    .rbs-archive .marker-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: 4px solid white;
    }

    .rbs-archive .marker-tooltip {
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

    .rbs-archive .map-marker:hover .marker-tooltip {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    .rbs-archive .tooltip-title {
        font-size: 12px;
        font-weight: 600;
        color: #111827;
    }

    .rbs-archive .tooltip-price {
        font-size: 12px;
        color: #0f766e;
        font-weight: 700;
    }

    /* Footer */
    .rbs-archive footer {
        background-color: #111827;
        color: white;
        margin-top: 64px;
    }

    .rbs-archive .footer-content {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        padding: 32px 0;
    }

    .rbs-archive .footer-copyright {
        color: #9ca3af;
        margin-bottom: 16px;
    }

    .rbs-archive .footer-brand {
        color: #5eead4;
        font-weight: 600;
    }

    .rbs-archive .footer-social {
        display: flex;
        gap: 24px;
    }

    .rbs-archive .social-link {
        color: #9ca3af;
        transition: color 0.3s;
    }

    .rbs-archive .social-link:hover {
        color: #5eead4;
    }

    /* Utility Classes */
    .rbs-archive .hidden {
        display: none;
    }

    .rbs-archive .flex {
        display: flex;
    }

    .rbs-archive .items-center {
        align-items: center;
    }

    .rbs-archive .justify-between {
        justify-content: space-between;
    }

    .rbs-archive .text-center {
        text-align: center;
    }

    .rbs-archive .w-full {
        width: 100%;
    }

    .rbs-archive .h-full {
        height: 100%;
    }

    .rbs-archive .ring-4 {
        box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.5);
    }

    .rbs-archive .ring-teal-500 {
        box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.5);
    }

    /* Responsive Styles */
    @media (min-width: 768px) {
        .rbs-archive .container {
            padding: 0 24px;
        }
        
        .rbs-archive .desktop-nav {
            display: flex;
        }
        
        .rbs-archive .mobile-menu-btn {
            display: none;
        }
        
        .rbs-archive .search-container {
            flex-direction: row;
        }
        
        .rbs-archive .control-bar {
            flex-direction: row;
            align-items: center;
        }
        
        .rbs-archive .property-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .rbs-archive .listings-container {
            grid-template-columns: 1fr 1fr;
        }
        
        .rbs-archive .dropdown-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .rbs-archive .checkbox-grid {
            grid-template-columns: repeat(4, 1fr);
        }
        
        .rbs-archive .footer-content {
            flex-direction: row;
        }
        
        .rbs-archive .footer-copyright {
            margin-bottom: 0;
        }
    }

    @media (min-width: 1024px) {
        .rbs-archive .container {
            padding: 0 24px;
        }
    }

    /* Page Transition */
    .rbs-archive .page-transition {
        animation: rbs-archive-fadeIn 0.5s ease;
    }

    @keyframes rbs-archive-fadeIn {
        from { 
            opacity: 0; 
            transform: translateY(20px); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0); 
        }
    }

    /* Skeleton Loading */
    .rbs-archive .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: rbs-archive-loading 1.5s infinite;
    }

    @keyframes rbs-archive-loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>


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

                <button class="filter-toggle">
                    <i class="fas fa-sliders-h"></i>
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
            const button = event.target.closest('.filter-chip');

            // Close all other dropdowns
            allDropdowns.forEach(dd => {
                if (dd.id !== dropdownId) {
                    dd.style.display = 'none';
                }
            });

            // Toggle current dropdown
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
                activeDropdown = null;
            } else {
                dropdown.style.display = 'block';
                activeDropdown = dropdown;
                
                // Position dropdown relative to button
                if (button) {
                    const buttonRect = button.getBoundingClientRect();
                    const containerRect = document.querySelector('.dropdowns-container').getBoundingClientRect();
                    
                    dropdown.style.position = 'absolute';
                    dropdown.style.left = (buttonRect.left - containerRect.left) + 'px';
                    dropdown.style.top = '100%';
                    dropdown.style.right = 'auto';
                    dropdown.style.width = 'auto';
                    dropdown.style.minWidth = '200px';
                }
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
                        dropdown.style.display = 'none';
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
                propertyGrid.className = 'property-grid';
                propertyGrid.style.gridTemplateColumns = '1fr';

                // For larger screens, use 2 columns
                if (window.innerWidth >= 768) {
                    propertyGrid.style.gridTemplateColumns = 'repeat(2, 1fr)';
                }
            } else {
                columnBtn.classList.add('active');
                gridBtn.classList.remove('active');
                propertyGrid.className = 'property-grid';
                propertyGrid.style.gridTemplateColumns = '1fr';
            }
        }

        // Toggle View (List/Map)
        function toggleView(view) {
            const viewBtns = document.querySelectorAll('.view-btn');
            viewBtns.forEach(btn => {
                btn.classList.remove('active');
            });

            event.target.closest('.view-btn').classList.add('active');

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
        document.querySelectorAll('.favorite-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const heart = this.querySelector('i');
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

        // Initialize layout for different screen sizes
        window.addEventListener('resize', function() {
            const gridBtn = document.getElementById('gridBtn');
            if (gridBtn.classList.contains('active')) {
                changeLayout('grid');
            }
        });

        // Initialize on page load
        window.addEventListener('DOMContentLoaded', function() {
            changeLayout('grid');
        });
    </script>
</div>

<?php get_footer(); ?>
