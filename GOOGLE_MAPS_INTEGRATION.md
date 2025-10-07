# Google Maps Integration

## Overview

The Google Maps integration provides a comprehensive mapping solution for the RealEstate Booking Suite plugin. It includes interactive property maps, location-based search, dynamic filtering, and customizable markers with full translation support.

## Core Features

### 1. Interactive Property Maps
- **Property Markers**: Display all properties with custom markers
- **Info Windows**: Rich property information with images and actions
- **Marker Clustering**: Group nearby markers for better performance
- **Custom Markers**: Support for custom marker icons and colors

### 2. Location-Based Search
- **Google Places Integration**: Search by location using Google Places API
- **Geocoding**: Convert addresses to coordinates
- **Area Search**: Find properties within map bounds
- **Autocomplete**: Smart location suggestions

### 3. Dynamic Filtering
- **Real-time Updates**: Markers update as filters change
- **Property Type Filter**: Filter by property type
- **Price Range Filter**: Filter by minimum and maximum price
- **Status Filter**: Filter by property status (for sale, for rent, sold, etc.)
- **Bedroom/Bathroom Filter**: Filter by number of bedrooms and bathrooms

### 4. Multiple Display Options
- **Widget**: WordPress Appearance widget for sidebars
- **Shortcode**: `[resbs_property_map]` for any page or post
- **Single Property**: Automatic map display on property pages
- **Elementor Integration**: Map widget for Elementor page builder

## Installation & Setup

### 1. Google Maps API Key
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable the following APIs:
   - Maps JavaScript API
   - Places API
   - Geocoding API
4. Create credentials (API Key)
5. Restrict the API key to your domain for security

### 2. Plugin Configuration
1. Go to **Properties > Map Settings** in WordPress admin
2. Enter your Google Maps API Key
3. Configure default map settings:
   - Default latitude and longitude
   - Default zoom level
   - Map style (default, satellite, hybrid, terrain)
4. Enable desired features:
   - Marker clustering
   - Search box
   - Property filters

## Usage

### 1. WordPress Widget
1. Go to **Appearance > Widgets**
2. Add "Property Map" widget to any widget area
3. Configure widget settings:
   - Title
   - Map height
   - Zoom level
   - Enable/disable features

### 2. Shortcode
Use the shortcode in any page or post:

```php
[resbs_property_map height="400px" zoom="12" show_search="true" show_filters="true"]
```

**Shortcode Parameters:**
- `height`: Map height (e.g., "400px", "50vh")
- `width`: Map width (default: "100%")
- `zoom`: Zoom level (1-20)
- `lat`: Center latitude
- `lng`: Center longitude
- `show_search`: Show search box (true/false)
- `show_filters`: Show filters (true/false)
- `cluster_markers`: Enable marker clustering (true/false)

### 3. Single Property Pages
Maps are automatically displayed on single property pages if the property has latitude and longitude coordinates.

### 4. Elementor Integration
1. Add "Property Map" widget to your Elementor page
2. Configure map settings in the widget panel
3. Customize appearance and behavior

## Customization

### 1. Map Styles
Customize map appearance with Google Maps styles:

```php
// Add custom styles in your theme's functions.php
add_filter('resbs_map_styles', function($styles) {
    $styles[] = array(
        'featureType' => 'poi',
        'elementType' => 'labels',
        'stylers' => array(
            array('visibility' => 'off')
        )
    );
    return $styles;
});
```

### 2. Custom Marker Icons
Set custom marker icons in the admin settings or programmatically:

```php
// Set custom marker icon
update_option('resbs_map_marker_icon', 'https://yoursite.com/custom-marker.png');
```

### 3. Info Window Content
Customize info window content with filters:

```php
// Customize info window content
add_filter('resbs_info_window_content', function($content, $property) {
    // Modify content based on property data
    return $content;
}, 10, 2);
```

### 4. Map Controls
Add custom map controls:

```php
// Add custom map control
add_action('resbs_map_controls', function($map_id) {
    // Add custom control HTML
});
```

## API Reference

### 1. JavaScript API
Access map functionality via JavaScript:

```javascript
// Get map instance
const map = RESBSMaps.getMap('map-id');

// Load properties
RESBSMaps.loadMapProperties($('#map-id'), 'map-id');

// Clear markers
RESBSMaps.clearMapMarkers('map-id');
```

### 2. PHP Hooks

#### Actions
- `resbs_property_map_display`: Display property map
- `resbs_map_controls`: Add custom map controls
- `resbs_map_after_load`: After map loads

#### Filters
- `resbs_map_styles`: Customize map styles
- `resbs_info_window_content`: Customize info window content
- `resbs_map_marker_icon`: Customize marker icons
- `resbs_map_properties_query`: Modify properties query

### 3. AJAX Endpoints
- `resbs_get_map_properties`: Get properties for map bounds
- `resbs_search_map_area`: Search location and get coordinates

## Security Features

### 1. Input Sanitization
- All user inputs are sanitized using WordPress functions
- SQL injection prevention with prepared statements
- XSS protection with proper escaping

### 2. Nonce Verification
- All AJAX requests include nonce verification
- CSRF protection for all form submissions

### 3. Capability Checks
- Admin settings require `manage_options` capability
- User-specific features check appropriate permissions

### 4. API Key Security
- API keys are stored securely in WordPress options
- Recommendations for API key restrictions provided

## Translation Support

### 1. Translation-Ready
All user-facing text is wrapped in translation functions:
- `esc_html_e()` for displayed text
- `esc_attr_e()` for HTML attributes
- `__()` for dynamic text

### 2. Translation Files
Translation files can be created for:
- Map interface text
- Error messages
- Button labels
- Form labels

### 3. RTL Support
CSS includes RTL (Right-to-Left) support for Arabic, Hebrew, and other RTL languages.

## Performance Optimization

### 1. Lazy Loading
- Maps only load when needed
- Assets enqueued conditionally
- Google Maps API loaded only when required

### 2. Caching
- Property data cached for better performance
- Map bounds queries optimized
- Marker clustering for large datasets

### 3. Responsive Design
- Mobile-optimized interface
- Touch-friendly controls
- Adaptive layouts for different screen sizes

## Troubleshooting

### 1. Common Issues

#### Map Not Loading
- Check API key configuration
- Verify API key restrictions
- Check browser console for errors

#### No Properties Showing
- Verify property coordinates are set
- Check property status (published)
- Verify map bounds include properties

#### Search Not Working
- Ensure Places API is enabled
- Check API key permissions
- Verify geocoding API is enabled

### 2. Debug Mode
Enable debug mode for troubleshooting:

```php
// Add to wp-config.php
define('RESBS_MAPS_DEBUG', true);
```

### 3. Error Logging
Check WordPress error logs for detailed error information.

## Browser Support

### 1. Supported Browsers
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Internet Explorer 11 (limited support)

### 2. Mobile Support
- iOS Safari 12+
- Chrome Mobile 60+
- Samsung Internet 8+

## Accessibility

### 1. Keyboard Navigation
- All map controls accessible via keyboard
- Tab order properly managed
- Focus indicators visible

### 2. Screen Reader Support
- ARIA labels for map controls
- Alt text for map images
- Semantic HTML structure

### 3. High Contrast Mode
- Support for high contrast themes
- Customizable colors for accessibility

## Future Enhancements

### 1. Planned Features
- Street View integration
- Directions to property
- Property comparison on map
- Heat map visualization
- Custom map layers

### 2. Integration Opportunities
- Weather data overlay
- School district boundaries
- Transportation routes
- Neighborhood information

## Support

For technical support and feature requests:
1. Check the documentation
2. Review troubleshooting section
3. Contact plugin support
4. Submit feature requests

## Changelog

### Version 1.0.0
- Initial release
- Basic map functionality
- Property markers
- Search and filtering
- Widget and shortcode support
- Translation-ready
- Security features implemented
