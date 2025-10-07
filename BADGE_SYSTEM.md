# Badge System Documentation

## Overview
The Badge System is a comprehensive solution for displaying customizable property badges throughout the RealEstate Booking Suite plugin. It supports Featured, New, and Sold badges with full admin customization capabilities.

## Features

### 🏷️ **Badge Types**
- **Featured Badge**: Highlights premium properties
- **New Badge**: Marks recently added properties
- **Sold Badge**: Indicates sold properties

### 🎨 **Customization Options**
- **Colors**: Background and text colors for each badge type
- **Text**: Customizable badge text (fully translatable)
- **Position**: Top-left, top-right, bottom-left, bottom-right
- **Size**: Small, medium, large
- **Border Radius**: Customizable corner rounding (0-50px)

### 📍 **Display Locations**
- Property cards in widgets
- Single property pages
- Elementor carousels
- Map markers
- Shortcodes
- Custom widgets

## Admin Configuration

### Accessing Badge Settings
1. Go to **Properties > Badge Settings** in WordPress admin
2. Configure each badge type individually
3. Preview changes in real-time
4. Save settings to apply changes

### Badge Configuration Options

#### Enable/Disable Badges
- Toggle individual badge types on/off
- Disabled badges won't appear anywhere

#### Text Customization
- Custom text for each badge type
- Fully translatable using WordPress translation functions
- Default texts: "Featured", "New", "Sold"

#### Color Customization
- Background color picker
- Text color picker
- Real-time preview updates
- Default colors:
  - Featured: Orange (#ff6b35)
  - New: Green (#28a745)
  - Sold: Red (#dc3545)

#### Position Options
- **Top Left**: Default position
- **Top Right**: Alternative top position
- **Bottom Left**: Bottom corner positioning
- **Bottom Right**: Alternative bottom position

#### Size Options
- **Small**: Compact badges (10px font, 2px padding)
- **Medium**: Standard badges (12px font, 4px padding)
- **Large**: Prominent badges (14px font, 6px padding)

#### Border Radius
- Range: 0-50 pixels
- 0 = Square corners
- Higher values = More rounded corners
- Default: 4px

## Property Management

### Adding Badges to Properties
1. Edit any property in WordPress admin
2. Find the "Property Badges" meta box in the sidebar
3. Check the badges you want to apply
4. Save the property

### Badge Meta Fields
- `_property_featured`: Set to "yes" for featured badge
- `_property_new`: Set to "yes" for new badge
- `_property_sold`: Set to "yes" for sold badge

## Display Integration

### Widget Integration
Badges automatically appear in the Property Grid widget when enabled:

```php
<?php if ($show_badges): ?>
    <?php do_action('resbs_property_badges', $property_id, 'widget'); ?>
<?php endif; ?>
```

### Single Property Pages
Badges display in the property header:

```php
<div class="resbs-badges-single">
    <?php do_action('resbs_property_badges', $property_id, 'single'); ?>
</div>
```

### Elementor Integration
Badges work seamlessly with Elementor widgets:

```php
<?php do_action('resbs_property_badges', get_the_ID(), 'elementor'); ?>
```

## Shortcode Usage

### Basic Badge Shortcode
```
[resbs_badges]
```

### Advanced Shortcode Options
```
[resbs_badges property_id="123" type="featured" context="shortcode"]
```

#### Shortcode Parameters
- `property_id`: Specific property ID (default: current property)
- `type`: Badge type filter (all, featured, new, sold)
- `context`: Display context (shortcode, widget, single, etc.)
- `show`: Show/hide badges (true/false)

## Widget Usage

### Badge Widget
A dedicated widget for displaying property badges:

1. Go to **Appearance > Widgets**
2. Find "Property Badges" widget
3. Drag to desired widget area
4. Configure:
   - Title
   - Property ID (leave empty for current property)
   - Badge type filter
   - Display context

## Developer Integration

### Action Hooks

#### Display Badges
```php
do_action('resbs_property_badges', $property_id, $context);
```

#### Filter Badge Text
```php
add_filter('resbs_badge_text_featured', function($text) {
    return 'Premium Property';
});
```

### Helper Functions

#### Check if Property Has Badge
```php
if (resbs_property_has_badge($property_id, 'featured')) {
    // Property is featured
}
```

#### Get Badge Count
```php
$badge_count = resbs_get_property_badge_count($property_id);
```

#### Get Badge HTML
```php
$badge_html = resbs_get_property_badges_html($property_id, 'card');
```

### CSS Classes

#### Badge Container
- `.resbs-property-badges` - Main badge container
- `.resbs-badges-single` - Single property page badges
- `.resbs-badges-carousel` - Carousel context badges
- `.resbs-badges-map` - Map marker badges

#### Individual Badges
- `.resbs-badge` - Base badge class
- `.resbs-badge-featured` - Featured badge
- `.resbs-badge-new` - New badge
- `.resbs-badge-sold` - Sold badge

#### Size Classes
- `.resbs-badge-small` - Small badge
- `.resbs-badge-medium` - Medium badge
- `.resbs-badge-large` - Large badge

#### Position Classes
- `.resbs-badge-top-left` - Top left position
- `.resbs-badge-top-right` - Top right position
- `.resbs-badge-bottom-left` - Bottom left position
- `.resbs-badge-bottom-right` - Bottom right position

## Customization

### CSS Customization
Override badge styles in your theme:

```css
.resbs-badge-featured {
    background-color: #your-color !important;
    color: #your-text-color !important;
}
```

### JavaScript Integration
Badges work with existing JavaScript functionality:

```javascript
// Badge click events
$('.resbs-badge').on('click', function() {
    // Custom badge interaction
});
```

## Translation Support

### Text Domain
All badge text uses the `realestate-booking-suite` text domain.

### Translatable Strings
- Badge labels in admin
- Default badge texts
- Admin interface text
- Help text and descriptions

### Translation Files
Create translation files in:
```
/wp-content/languages/plugins/realestate-booking-suite-[locale].po
```

## Security Features

### Input Sanitization
- All admin inputs are sanitized
- Color values validated as hex colors
- Numeric values validated within ranges
- Text inputs sanitized with `sanitize_text_field()`

### Output Escaping
- All badge text escaped with `esc_html()`
- CSS attributes escaped with `esc_attr()`
- URLs escaped with `esc_url()`

### Nonce Protection
- Admin forms protected with nonces
- AJAX requests verified with nonces

## Performance

### Dynamic CSS Generation
- Badge styles generated dynamically
- Cached in uploads directory
- Versioned for cache busting

### Conditional Loading
- CSS only loaded when badges are active
- JavaScript only loaded on admin pages
- Minimal database queries

## Browser Support
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Internet Explorer 11 (with polyfills)

## Troubleshooting

### Badges Not Appearing
1. Check if badges are enabled in settings
2. Verify property has badge meta fields set
3. Check for CSS conflicts
4. Clear any caching plugins

### Styling Issues
1. Check for theme CSS conflicts
2. Verify color picker values are valid hex colors
3. Clear browser cache
4. Check responsive design breakpoints

### Translation Issues
1. Verify text domain is correct
2. Check translation file location
3. Ensure translation functions are used
4. Test with different locales

## API Reference

### Classes
- `RESBS_Badge_Manager` - Main badge management class
- `RESBS_Badge_Widget` - WordPress widget for badges

### Functions
- `resbs_display_property_badges()` - Display badges
- `resbs_get_property_badges_html()` - Get badge HTML
- `resbs_property_has_badge()` - Check badge existence
- `resbs_get_property_badge_count()` - Count badges
- `resbs_badge_shortcode()` - Shortcode handler

### Hooks
- `resbs_property_badges` - Display badges action
- `resbs_badge_text_[type]` - Filter badge text
- `resbs_badge_settings` - Filter badge settings

## Examples

### Custom Badge Styling
```css
/* Custom featured badge */
.resbs-badge-featured {
    background: linear-gradient(45deg, #ff6b35, #f7931e);
    box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
```

### Custom Badge Text
```php
// Change featured badge text
add_filter('resbs_badge_text_featured', function($text) {
    return 'Premium';
});

// Change new badge text
add_filter('resbs_badge_text_new', function($text) {
    return 'Just Added';
});
```

### Conditional Badge Display
```php
// Only show badges on single property pages
add_action('resbs_property_badges', function($property_id, $context) {
    if ($context === 'single') {
        // Custom badge logic
    }
}, 10, 2);
```
