# Shortcodes Documentation

## Overview

The RealEstate Booking Suite plugin provides comprehensive shortcodes that allow you to display plugin features anywhere in your theme or Elementor pages. All shortcodes are fully responsive, translation-ready, and include proper escaping and sanitization for security.

## Available Shortcodes

### 1. Property Grid Shortcode

**Shortcode:** `[resbs_property_grid]`

**Description:** Displays properties in a grid, list, or carousel layout with filtering options.

**Attributes:**
- `title` (string) - Widget title (default: "Properties")
- `posts_per_page` (integer) - Number of properties to display (default: 12)
- `columns` (integer) - Number of columns for grid layout (default: 3)
- `layout` (string) - Layout type: grid, list, carousel (default: "grid")
- `show_filters` (boolean) - Show filter options (default: "yes")
- `show_price` (boolean) - Show property price (default: "yes")
- `show_meta` (boolean) - Show property meta information (default: "yes")
- `show_excerpt` (boolean) - Show property excerpt (default: "yes")
- `show_badges` (boolean) - Show property badges (default: "yes")
- `show_favorite_button` (boolean) - Show favorite button (default: "yes")
- `show_book_button` (boolean) - Show book/view button (default: "yes")
- `orderby` (string) - Order by: date, title, price, rand (default: "date")
- `order` (string) - Order: ASC, DESC (default: "DESC")
- `property_type` (string) - Filter by property type slug
- `property_status` (string) - Filter by property status slug
- `featured_only` (boolean) - Show only featured properties (default: "no")
- `carousel_autoplay` (boolean) - Enable carousel autoplay (default: "no")
- `carousel_autoplay_speed` (integer) - Autoplay speed in milliseconds (default: 3000)
- `carousel_show_dots` (boolean) - Show carousel dots (default: "yes")
- `carousel_show_arrows` (boolean) - Show carousel arrows (default: "yes")
- `enable_infinite_scroll` (boolean) - Enable infinite scroll (default: "no")
- `show_pagination` (boolean) - Show pagination (default: "yes")

**Examples:**
```php
// Basic grid
[resbs_property_grid]

// Custom grid with filters
[resbs_property_grid title="Featured Properties" posts_per_page="8" columns="4" featured_only="yes"]

// Carousel layout
[resbs_property_grid layout="carousel" carousel_autoplay="yes" carousel_autoplay_speed="4000"]

// List layout with specific property type
[resbs_property_grid layout="list" property_type="apartment" show_filters="no"]
```

### 2. Property List Shortcode

**Shortcode:** `[resbs_property_list]`

**Description:** Displays properties in a list layout. This is an alias for the property grid shortcode with `layout="list"`.

**Attributes:** Same as `[resbs_property_grid]` but automatically sets `layout="list"`

**Examples:**
```php
// Basic list
[resbs_property_list]

// Custom list
[resbs_property_list title="Recent Properties" posts_per_page="6" show_filters="yes"]
```

### 3. Search Shortcode

**Shortcode:** `[resbs_search]`

**Description:** Provides a comprehensive property search interface with map integration.

**Attributes:**
- `title` (string) - Widget title (default: "Search Properties")
- `show_map` (boolean) - Show map (default: "yes")
- `show_filters` (boolean) - Show search filters (default: "yes")
- `show_results` (boolean) - Show search results (default: "yes")
- `results_per_page` (integer) - Results per page (default: 12)
- `map_height` (integer) - Map height in pixels (default: 400)

**Examples:**
```php
// Full search with map
[resbs_search]

// Search without map
[resbs_search show_map="no" map_height="300"]

// Search with custom title
[resbs_search title="Find Your Dream Home" results_per_page="20"]
```

### 4. Dashboard Shortcode

**Shortcode:** `[resbs_dashboard]`

**Description:** Displays a user dashboard with tabs for properties, favorites, bookings, and profile management.

**Attributes:**
- `title` (string) - Widget title (default: "My Dashboard")
- `show_properties` (boolean) - Show properties tab (default: "yes")
- `show_favorites` (boolean) - Show favorites tab (default: "yes")
- `show_bookings` (boolean) - Show bookings tab (default: "yes")
- `show_profile` (boolean) - Show profile tab (default: "yes")

**Examples:**
```php
// Full dashboard
[resbs_dashboard]

// Dashboard with only properties and favorites
[resbs_dashboard show_bookings="no" show_profile="no"]

// Custom dashboard title
[resbs_dashboard title="My Account"]
```

### 5. Submit Property Shortcode

**Shortcode:** `[resbs_submit_property]`

**Description:** Provides a form for users to submit new properties.

**Attributes:**
- `title` (string) - Widget title (default: "Submit Property")
- `show_gallery` (boolean) - Show gallery upload (default: "yes")
- `show_map` (boolean) - Show map for location selection (default: "yes")
- `show_amenities` (boolean) - Show amenities section (default: "yes")
- `show_video` (boolean) - Show video URL field (default: "yes")

**Examples:**
```php
// Full submit form
[resbs_submit_property]

// Submit form without gallery
[resbs_submit_property show_gallery="no"]

// Custom title
[resbs_submit_property title="List Your Property"]
```

### 6. Favorites Shortcode

**Shortcode:** `[resbs_favorites]`

**Description:** Displays user's favorite properties.

**Attributes:**
- `title` (string) - Widget title (default: "My Favorite Properties")
- `layout` (string) - Layout type: grid, list, carousel (default: "grid")
- `columns` (integer) - Number of columns for grid layout (default: 3)
- `show_price` (boolean) - Show property price (default: "yes")
- `show_meta` (boolean) - Show property meta information (default: "yes")
- `show_excerpt` (boolean) - Show property excerpt (default: "yes")
- `show_badges` (boolean) - Show property badges (default: "yes")

**Examples:**
```php
// Basic favorites
[resbs_favorites]

// Favorites in list layout
[resbs_favorites layout="list" show_excerpt="no"]

// Custom favorites display
[resbs_favorites title="My Saved Properties" columns="2"]
```

## Usage Examples

### In WordPress Posts/Pages

```php
// Add to any post or page content
[resbs_property_grid title="Featured Properties" posts_per_page="6" featured_only="yes"]

[resbs_search title="Find Properties" show_map="yes"]

[resbs_dashboard title="My Account"]
```

### In Theme Templates

```php
// In your theme's PHP files
echo do_shortcode('[resbs_property_grid layout="carousel" carousel_autoplay="yes"]');

// With custom attributes
$shortcode = '[resbs_search title="Property Search" results_per_page="20"]';
echo do_shortcode($shortcode);
```

### In Elementor

1. Add a "Shortcode" widget to your page
2. Enter the shortcode with desired attributes
3. Customize styling through Elementor's design options

### In Widget Areas

```php
// Add to functions.php to create a custom widget
function my_custom_property_widget() {
    echo do_shortcode('[resbs_property_grid title="Latest Properties" posts_per_page="4"]');
}
add_action('wp_footer', 'my_custom_property_widget');
```

## Styling and Customization

### CSS Classes

All shortcodes include CSS classes for easy styling:

```css
/* Main shortcode container */
.resbs-shortcode

/* Widget title */
.resbs-widget-title

/* Property grid */
.resbs-property-grid-widget.resbs-shortcode

/* Search widget */
.resbs-search-widget

/* Dashboard widget */
.resbs-dashboard-widget

/* Submit widget */
.resbs-submit-widget

/* Favorites widget */
.resbs-favorites-widget
```

### Custom CSS

```css
/* Customize shortcode appearance */
.resbs-shortcode .resbs-widget-title {
    color: #your-color;
    font-size: 2rem;
}

.resbs-property-grid-widget.resbs-shortcode {
    background: #your-background;
    padding: 20px;
    border-radius: 8px;
}
```

### Responsive Design

All shortcodes are fully responsive and include:
- Mobile-first design approach
- Touch-friendly interactions
- Adaptive layouts for different screen sizes
- Optimized performance for mobile devices

## Security Features

### Input Sanitization
- All user inputs are sanitized using `RESBS_Security` helper class
- Form data is validated before processing
- File uploads are properly handled and validated

### Output Escaping
- All output is properly escaped using WordPress functions
- Translation strings are escaped with `esc_html__()`, `esc_attr__()`, etc.
- URLs are escaped with `esc_url()`

### Nonce Verification
- All AJAX requests include nonce verification
- Forms include nonce fields for security
- User capabilities are checked for sensitive operations

## Translation Support

All shortcode text is translation-ready:

```php
// Example of translation-ready text
esc_html__('Properties', 'realestate-booking-suite')
esc_html__('Search Properties', 'realestate-booking-suite')
esc_html__('My Dashboard', 'realestate-booking-suite')
```

### Adding Translations

1. Use a translation plugin like Loco Translate
2. Create translation files for your language
3. Translate all strings with the text domain `realestate-booking-suite`

## AJAX Functionality

### Available AJAX Actions

- `resbs_filter_properties` - Filter properties based on criteria
- `resbs_search_properties` - Search properties with map integration
- `resbs_update_profile` - Update user profile information
- `resbs_submit_property` - Submit new property
- `resbs_clear_favorites` - Clear user favorites
- `resbs_load_more_properties` - Load more properties for infinite scroll

### AJAX Response Format

```javascript
// Success response
{
    "success": true,
    "data": {
        "html": "...",
        "count": 10,
        "message": "Success message"
    }
}

// Error response
{
    "success": false,
    "data": {
        "message": "Error message"
    }
}
```

## Performance Considerations

### Optimization Features

- **Lazy Loading**: Images and content loaded on demand
- **Caching**: Query results cached for better performance
- **Minification**: CSS and JavaScript files are minified
- **CDN Ready**: Assets can be served from CDN

### Best Practices

1. **Limit Results**: Use `posts_per_page` to limit the number of properties displayed
2. **Use Filters**: Enable filters to reduce initial load
3. **Optimize Images**: Ensure property images are optimized
4. **Cache Results**: Use caching plugins for better performance

## Troubleshooting

### Common Issues

1. **Shortcode Not Displaying**
   - Check if the shortcode is properly registered
   - Verify the shortcode syntax
   - Check for JavaScript errors in browser console

2. **Styling Issues**
   - Ensure CSS files are enqueued
   - Check for theme conflicts
   - Verify CSS class names

3. **AJAX Not Working**
   - Check nonce verification
   - Verify AJAX URL is correct
   - Check for JavaScript errors

4. **Translation Issues**
   - Verify text domain is correct
   - Check if translation files exist
   - Ensure strings are properly wrapped

### Debug Mode

Enable debug mode in WordPress to see detailed error messages:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Browser Support

### Supported Browsers
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- iOS Safari 12+
- Chrome Mobile 60+

### Feature Support
- CSS Grid: Full support
- Flexbox: Full support
- ES6 JavaScript: Full support
- Touch Events: Full support
- AJAX: Full support

## Future Enhancements

### Planned Features
- **Advanced Filters**: More filter options
- **Custom Fields**: Support for custom property fields
- **Multi-language**: Enhanced multi-language support
- **API Integration**: REST API endpoints for shortcodes
- **Analytics**: Built-in analytics for shortcode usage

### Customization Options
- **Custom Templates**: Override default templates
- **Hook System**: Action and filter hooks for customization
- **Theme Integration**: Better theme integration options
- **Performance Monitoring**: Built-in performance monitoring

## Support and Maintenance

### Regular Updates
- **Security Updates**: Regular security patches
- **Feature Updates**: New features and improvements
- **Bug Fixes**: Regular bug fixes and improvements
- **Compatibility**: WordPress and theme compatibility updates

### Documentation
- **User Guide**: Comprehensive user documentation
- **Developer Guide**: Developer documentation and examples
- **Video Tutorials**: Video tutorials for common tasks
- **FAQ**: Frequently asked questions and answers

## Conclusion

The RealEstate Booking Suite shortcodes provide a powerful and flexible way to display property-related content anywhere on your website. With comprehensive customization options, security features, and responsive design, these shortcodes are suitable for any real estate website.

For additional support or customization requests, please refer to the plugin documentation or contact support.
