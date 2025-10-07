# Favorites System

## Overview

The Favorites System allows users to save and manage their preferred properties. It provides a comprehensive solution for user engagement with properties, including a dedicated shortcode, widget, and integration throughout the plugin interface.

## Core Features

### 1. User Favorites Management
- **Add/Remove Properties**: Users can add or remove properties from their favorites
- **Persistent Storage**: Favorites are saved for logged-in users and in sessions for guests
- **Real-time Updates**: AJAX-powered favorite toggling without page refresh
- **Visual Feedback**: Clear visual indicators for favorite status

### 2. Multiple Display Options
- **Shortcode**: `[resbs_favorites]` for displaying user favorites
- **Widget**: WordPress Appearance widget for sidebars
- **Property Cards**: Favorite buttons on all property cards
- **Single Property Pages**: Favorite buttons on individual property pages

### 3. User Experience Features
- **Login Integration**: Prompts users to login when needed
- **Empty State**: Helpful message when no favorites exist
- **Clear All**: Option to clear all favorites at once
- **Responsive Design**: Works perfectly on all devices

## Installation & Setup

### 1. Automatic Integration
The favorites system is automatically integrated into the plugin and requires no additional setup. It works out of the box with:
- Property Grid widget
- Single property pages
- Property cards throughout the site

### 2. User Requirements
- **Logged-in Users**: Favorites are saved to user meta and persist across sessions
- **Guest Users**: Favorites are saved in browser session and persist during the session

## Usage

### 1. Shortcode Usage
Display user favorites anywhere on your site:

```php
[resbs_favorites]
```

**Shortcode Parameters:**
- `layout`: Display layout (grid, list) - default: "grid"
- `columns`: Number of columns (1-4) - default: "3"
- `show_image`: Show property images (true/false) - default: "true"
- `show_price`: Show property prices (true/false) - default: "true"
- `show_details`: Show property details (true/false) - default: "true"
- `show_actions`: Show action buttons (true/false) - default: "true"
- `show_clear_button`: Show clear all button (true/false) - default: "true"
- `posts_per_page`: Number of properties to show - default: "12"
- `orderby`: Sort order (date, title, price) - default: "date"
- `order`: Sort direction (ASC, DESC) - default: "DESC"

**Examples:**
```php
// Basic usage
[resbs_favorites]

// Grid layout with 4 columns
[resbs_favorites layout="grid" columns="4"]

// List layout without clear button
[resbs_favorites layout="list" show_clear_button="false"]

// Show only 6 properties
[resbs_favorites posts_per_page="6"]
```

### 2. Widget Usage
1. Go to **Appearance > Widgets**
2. Add "Property Favorites" widget to any widget area
3. Configure widget settings:
   - Title
   - Maximum properties to show
   - Show favorites count
   - Show clear button

### 3. Property Card Integration
Favorite buttons are automatically added to:
- Property Grid widget cards
- Single property pages
- Any property card using the `resbs_property_favorite_button` action

### 4. Custom Integration
Add favorite buttons to custom property displays:

```php
// Display favorite button
do_action('resbs_property_favorite_button', $property_id, 'custom');

// Check if property is favorite
$favorites_manager = new RESBS_Favorites_Manager();
$is_favorite = $favorites_manager->is_favorite($property_id);

// Get user's favorites count
$count = $favorites_manager->get_favorites_count();
```

## Customization

### 1. Styling
Customize the appearance with CSS:

```css
/* Custom favorite button style */
.resbs-favorite-btn {
    background: #your-color;
    border-radius: 50%;
}

/* Custom favorites grid */
.resbs-favorites-grid {
    gap: 30px;
}

/* Custom empty state */
.resbs-favorites-empty {
    background: #your-background;
}
```

### 2. JavaScript Integration
Access favorites functionality via JavaScript:

```javascript
// Toggle favorite
RESBSFavorites.toggleFavorite($('.resbs-favorite-btn'));

// Get favorites count
const count = RESBSFavorites.getCount();

// Load favorites into container
RESBSFavorites.loadFavorites($('#favorites-container'));
```

### 3. PHP Hooks

#### Actions
- `resbs_property_favorite_button`: Display favorite button
- `resbs_single_property_actions`: Add actions to single property pages
- `resbs_property_card_actions`: Add actions to property cards

#### Filters
- `resbs_favorites_query_args`: Modify favorites query
- `resbs_favorites_shortcode_atts`: Modify shortcode attributes
- `resbs_favorites_empty_message`: Customize empty state message

## Security Features

### 1. Input Sanitization
- All user inputs are sanitized using WordPress functions
- Property IDs are validated as integers
- Nonce verification for all AJAX requests

### 2. User Permissions
- Favorites are user-specific
- No cross-user data access
- Secure session handling for guests

### 3. Data Validation
- Property existence validation
- User capability checks
- SQL injection prevention

## Translation Support

### 1. Translation-Ready
All user-facing text is wrapped in translation functions:
- `esc_html_e()` for displayed text
- `esc_attr_e()` for HTML attributes
- `__()` for dynamic text

### 2. Translation Files
Translation files can be created for:
- Button labels
- Messages and notifications
- Empty state text
- Error messages

### 3. RTL Support
CSS includes RTL (Right-to-Left) support for Arabic, Hebrew, and other RTL languages.

## API Reference

### 1. AJAX Endpoints
- `resbs_toggle_favorite`: Add/remove property from favorites
- `resbs_get_favorites`: Get user's favorite properties
- `resbs_clear_favorites`: Clear all user favorites

### 2. JavaScript API
```javascript
// Toggle favorite for a property
RESBSFavorites.toggleFavorite($button);

// Remove property from favorites
RESBSFavorites.removeFavorite($button);

// Clear all favorites
RESBSFavorites.clearFavorites($button);

// Update favorites count
RESBSFavorites.updateCount();

// Load favorites into container
RESBSFavorites.loadFavorites($container);

// Get current favorites count
const count = RESBSFavorites.getCount();
```

### 3. PHP Methods
```php
// Check if property is favorite
$is_favorite = $favorites_manager->is_favorite($property_id);

// Get user's favorites
$favorites = $favorites_manager->get_user_favorites();

// Get favorites count
$count = $favorites_manager->get_favorites_count();

// Display favorite button
$favorites_manager->display_favorite_button($property_id, $context);
```

## Performance Optimization

### 1. Efficient Queries
- Optimized database queries for favorites
- Minimal AJAX requests
- Cached user meta data

### 2. Lazy Loading
- Assets loaded only when needed
- Conditional script enqueuing
- Optimized JavaScript execution

### 3. Responsive Design
- Mobile-optimized interface
- Touch-friendly buttons
- Adaptive layouts

## Troubleshooting

### 1. Common Issues

#### Favorites Not Saving
- Check if user is logged in
- Verify AJAX requests are working
- Check browser console for errors

#### Favorite Buttons Not Showing
- Ensure the action hooks are called
- Check if the property ID is valid
- Verify CSS is not hiding buttons

#### Empty Favorites Page
- Check if user has any favorites
- Verify property posts exist and are published
- Check for JavaScript errors

### 2. Debug Mode
Enable debug mode for troubleshooting:

```php
// Add to wp-config.php
define('RESBS_FAVORITES_DEBUG', true);
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
- All buttons accessible via keyboard
- Proper tab order
- Focus indicators visible

### 2. Screen Reader Support
- ARIA labels for buttons
- Semantic HTML structure
- Alt text for images

### 3. High Contrast Mode
- Support for high contrast themes
- Customizable colors for accessibility

## Future Enhancements

### 1. Planned Features
- Favorites sharing via email
- Favorites comparison tool
- Favorites notifications
- Bulk favorites management

### 2. Integration Opportunities
- Social media sharing
- Email marketing integration
- Analytics tracking
- User preferences

## Support

For technical support and feature requests:
1. Check the documentation
2. Review troubleshooting section
3. Contact plugin support
4. Submit feature requests

## Changelog

### Version 1.0.0
- Initial release
- Basic favorites functionality
- Shortcode and widget support
- Property card integration
- Translation-ready
- Security features implemented
- Responsive design
- Accessibility support
