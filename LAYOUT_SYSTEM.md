# Layout System Documentation

## Overview

The RealEstate Booking Suite plugin now includes a flexible layout system that allows both admin widgets and Elementor widgets to display properties in three different layouts: Grid, List, and Carousel. This system provides enhanced design flexibility and improved user experience.

## Features

### Layout Options

1. **Grid Layout**
   - Traditional card-based grid display
   - Responsive columns (1-4 columns)
   - Hover effects and animations
   - Consistent card heights

2. **List Layout**
   - Horizontal property cards
   - Image on the left, content on the right
   - Better for detailed property information
   - Responsive design (stacks on mobile)

3. **Carousel Layout**
   - Sliding carousel with navigation controls
   - Autoplay functionality
   - Dots and arrow navigation
   - Touch/swipe support
   - Keyboard navigation

### Carousel Features

- **Autoplay**: Automatic sliding with configurable speed
- **Navigation**: Previous/Next arrows and dot indicators
- **Responsive**: Adapts to different screen sizes
- **Touch Support**: Swipe gestures on mobile devices
- **Keyboard Navigation**: Arrow key support
- **Pause on Hover**: Stops autoplay when hovering

## Implementation

### Admin Widget

The Property Grid widget in WordPress admin now includes:

#### Layout Options
- Layout selection dropdown (Grid, List, Carousel)
- Conditional carousel settings that appear when carousel is selected

#### Carousel Settings
- Enable/disable autoplay
- Autoplay speed (1000-10000ms)
- Show/hide dots navigation
- Show/hide arrow navigation

#### Form Structure
```php
// Layout selection
<select name="layout">
    <option value="grid">Grid</option>
    <option value="list">List</option>
    <option value="carousel">Carousel</option>
</select>

// Carousel options (conditional)
<div class="resbs-carousel-options">
    <input type="checkbox" name="carousel_autoplay">
    <input type="number" name="carousel_autoplay_speed">
    <input type="checkbox" name="carousel_show_dots">
    <input type="checkbox" name="carousel_show_arrows">
</div>
```

### Elementor Widget

The Elementor Property Grid widget includes:

#### Content Section
- Title
- Number of properties
- Columns (for grid layout)
- Layout selection

#### Carousel Settings Section
- Autoplay toggle
- Autoplay speed
- Show dots toggle
- Show arrows toggle
- Infinite loop toggle
- Pause on hover toggle

#### Display Options Section
- Show/hide price
- Show/hide meta information
- Show/hide excerpt
- Show/hide badges
- Show/hide favorite button
- Show/hide book button

#### Query Section
- Order by options
- Order direction
- Property type filter
- Property status filter
- Featured properties only

## CSS Classes

### Layout Classes
- `.resbs-layout-grid` - Grid layout
- `.resbs-layout-list` - List layout
- `.resbs-layout-carousel` - Carousel layout

### Grid Classes
- `.resbs-grid-1-cols` - 1 column grid
- `.resbs-grid-2-cols` - 2 column grid
- `.resbs-grid-3-cols` - 3 column grid
- `.resbs-grid-4-cols` - 4 column grid

### Carousel Classes
- `.resbs-property-carousel` - Main carousel container
- `.resbs-carousel-wrapper` - Carousel wrapper
- `.resbs-carousel-track` - Carousel track
- `.resbs-carousel-prev` - Previous button
- `.resbs-carousel-next` - Next button
- `.resbs-carousel-dots` - Dots container
- `.resbs-carousel-dot` - Individual dot

### Property Card Classes
- `.resbs-property-card` - Property card container
- `.resbs-property-image` - Property image container
- `.resbs-property-content` - Property content container
- `.resbs-property-title` - Property title
- `.resbs-property-price` - Property price
- `.resbs-property-meta` - Property meta information
- `.resbs-property-excerpt` - Property excerpt
- `.resbs-property-actions` - Property action buttons

## JavaScript Functionality

### Carousel Initialization
```javascript
// Initialize carousel
function initializeCarousel($carousel) {
    // Set up autoplay
    // Configure navigation
    // Handle touch events
    // Set up keyboard navigation
}
```

### Layout Switching
```javascript
// Switch between layouts
function switchLayout(layout) {
    // Remove existing layout classes
    // Add new layout class
    // Reinitialize carousel if needed
}
```

### Responsive Handling
```javascript
// Handle responsive layouts
function updateResponsiveLayouts() {
    // Recalculate items per view
    // Update carousel position
    // Adjust card widths
}
```

## Data Attributes

### Widget Container
```html
<div class="resbs-property-grid-widget" 
     data-settings='{"layout":"grid","columns":3,...}'>
```

### Carousel Container
```html
<div class="resbs-property-carousel" 
     data-autoplay="true"
     data-autoplay-speed="3000"
     data-show-dots="true"
     data-show-arrows="true">
```

## Security Features

### Input Sanitization
All layout options are properly sanitized:

```php
// Sanitize layout selection
$layout = sanitize_text_field($instance['layout']);

// Sanitize carousel options
$carousel_autoplay = (bool) $instance['carousel_autoplay'];
$carousel_autoplay_speed = intval($instance['carousel_autoplay_speed']);
$carousel_show_dots = (bool) $instance['carousel_show_dots'];
$carousel_show_arrows = (bool) $instance['carousel_show_arrows'];
```

### Output Escaping
All output is properly escaped:

```php
// Escape layout class
echo esc_attr($layout);

// Escape data attributes
echo esc_attr($carousel_autoplay ? 'true' : 'false');
```

## Responsive Design

### Breakpoints
- **Desktop**: 1200px and above
- **Tablet**: 768px - 1199px
- **Mobile**: Below 768px

### Responsive Behavior
- **Grid**: Automatically adjusts columns based on screen size
- **List**: Stacks vertically on mobile devices
- **Carousel**: Adjusts items per view based on container width

### CSS Media Queries
```css
@media (max-width: 1200px) {
    .resbs-grid-4-cols {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 992px) {
    .resbs-grid-3-cols,
    .resbs-grid-4-cols {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .resbs-layout-grid .resbs-property-grid {
        grid-template-columns: 1fr;
    }
}
```

## Accessibility Features

### Keyboard Navigation
- Arrow keys for carousel navigation
- Tab navigation for all interactive elements
- Focus indicators for better visibility

### ARIA Labels
```html
<button class="resbs-carousel-prev" 
        aria-label="Previous">‹</button>
<button class="resbs-carousel-next" 
        aria-label="Next">›</button>
```

### Screen Reader Support
- Proper heading structure
- Alt text for images
- Descriptive link text
- Form labels

## Browser Support

### Modern Browsers
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

### Features
- CSS Grid support
- Flexbox support
- CSS Transitions
- Touch events
- ES6 JavaScript

## Performance Considerations

### CSS Optimization
- Efficient selectors
- Minimal repaints
- Hardware acceleration for animations

### JavaScript Optimization
- Debounced resize events
- Efficient DOM queries
- Event delegation

### Image Optimization
- Responsive images
- Lazy loading support
- Proper image sizing

## Customization

### CSS Custom Properties
```css
:root {
    --resbs-card-border-radius: 8px;
    --resbs-card-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    --resbs-carousel-transition: 0.5s ease;
}
```

### Theme Integration
The layout system is designed to work with any WordPress theme:

```css
/* Theme-specific overrides */
.resbs-property-card {
    border-radius: var(--theme-border-radius);
    box-shadow: var(--theme-card-shadow);
}
```

## Troubleshooting

### Common Issues

1. **Carousel not working**
   - Check if jQuery is loaded
   - Verify carousel container exists
   - Check for JavaScript errors

2. **Layout not switching**
   - Verify CSS files are enqueued
   - Check for CSS conflicts
   - Ensure proper class names

3. **Responsive issues**
   - Check viewport meta tag
   - Verify CSS media queries
   - Test on actual devices

### Debug Mode
Enable debug mode to see additional information:

```php
// Add to wp-config.php
define('RESBS_DEBUG', true);
```

## Future Enhancements

### Planned Features
- Masonry layout option
- Advanced carousel settings
- Layout presets
- Custom CSS editor
- Animation options

### API Extensions
- Custom layout hooks
- Filter system for layouts
- Action hooks for customization

## Support

For technical support or feature requests, please refer to the plugin documentation or contact the development team.

## Changelog

### Version 1.0.0
- Initial release of layout system
- Grid, List, and Carousel layouts
- Admin widget integration
- Elementor widget integration
- Responsive design
- Accessibility features
- Security implementation
