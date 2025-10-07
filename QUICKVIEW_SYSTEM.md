# Quick View System Documentation

## Overview
The Quick View System provides an AJAX-powered modal that allows users to preview property details without leaving the listing page. This enhances user experience and improves conversion rates by reducing page navigation friction.

## Features

### 🚀 **Core Functionality**
- **AJAX Loading**: Fast, seamless content loading without page refresh
- **Modal Interface**: Professional overlay modal with smooth animations
- **Gallery Support**: Image gallery with thumbnail navigation
- **Property Details**: Complete property information display
- **Interactive Elements**: Book Now and Favorites buttons
- **Map Integration**: Location preview with external map links

### 📱 **User Experience**
- **Responsive Design**: Works perfectly on all device sizes
- **Keyboard Navigation**: Full keyboard accessibility support
- **Touch Friendly**: Optimized for mobile and tablet interactions
- **Loading States**: Visual feedback during content loading
- **Error Handling**: Graceful error messages and fallbacks

### 🎨 **Visual Features**
- **Smooth Animations**: Fade in/out transitions and hover effects
- **Image Gallery**: Main image with thumbnail navigation
- **Property Badges**: Integration with badge system
- **Modern UI**: Clean, professional design
- **Customizable Styling**: Easy theme customization

## Technical Implementation

### Files Created
- `includes/class-resbs-quickview.php` - Main Quick View Manager class
- `assets/css/quickview.css` - Complete modal styling
- `assets/js/quickview.js` - JavaScript functionality
- `QUICKVIEW_SYSTEM.md` - This documentation

### Files Updated
- `includes/functions.php` - Added Quick View Manager loading
- `includes/class-resbs-widgets.php` - Integrated quick view buttons

## Quick View Content

### Property Information Displayed
1. **Property Title** - Clickable link to full property page
2. **Gallery** - Main image with thumbnail navigation
3. **Price** - Formatted price with currency
4. **Location** - Property address/location
5. **Meta Information** - Bedrooms, bathrooms, area
6. **Property Type & Status** - Taxonomy information
7. **Description** - Truncated property description
8. **Amenities** - Key amenities preview
9. **Map Preview** - Location with external map link
10. **Property Badges** - Featured, New, Sold badges

### Interactive Elements
- **View Full Details** - Link to complete property page
- **Book Now** - Direct booking action
- **Add to Favorites** - Toggle favorite status
- **Gallery Navigation** - Thumbnail and keyboard navigation
- **Map Preview** - External map integration

## Usage

### Automatic Integration
Quick View buttons are automatically added to property cards in:
- Property Grid Widget
- Elementor widgets
- Custom property listings
- Any template using the action hook

### Manual Integration
Add quick view buttons manually using action hooks:

```php
// Add quick view button to property card
do_action('resbs_property_card_after_content', $property_id, 'context');

// Add quick view button to property actions
do_action('resbs_property_card_actions', $property_id, 'context');
```

### JavaScript Integration
Access the Quick View Manager globally:

```javascript
// Open quick view programmatically
RESBSQuickView.openQuickView(propertyId);

// Close quick view
RESBSQuickView.closeQuickView();
```

## AJAX Endpoints

### Get Quick View Content
- **Action**: `resbs_get_quickview`
- **Method**: POST
- **Parameters**:
  - `property_id` (int) - Property ID
  - `nonce` (string) - Security nonce
- **Response**: JSON with property content HTML

### Toggle Favorite
- **Action**: `resbs_toggle_favorite`
- **Method**: POST
- **Parameters**:
  - `property_id` (int) - Property ID
  - `nonce` (string) - Security nonce
- **Response**: JSON with favorite status

## CSS Classes

### Modal Structure
- `.resbs-quickview-modal` - Main modal container
- `.resbs-quickview-overlay` - Modal backdrop
- `.resbs-quickview-container` - Modal content wrapper
- `.resbs-quickview-wrapper` - Modal content container

### Content Sections
- `.resbs-quickview-header` - Modal header with title and close button
- `.resbs-quickview-body` - Main content area
- `.resbs-quickview-footer` - Action buttons area

### Gallery
- `.resbs-quickview-gallery` - Gallery container
- `.resbs-quickview-main-image` - Main image display
- `.resbs-quickview-thumbnails` - Thumbnail navigation
- `.resbs-quickview-thumb` - Individual thumbnail

### Property Info
- `.resbs-quickview-info` - Property information container
- `.resbs-quickview-price` - Price display
- `.resbs-quickview-location` - Location information
- `.resbs-quickview-meta` - Meta information (bedrooms, bathrooms, area)
- `.resbs-quickview-description` - Property description
- `.resbs-quickview-amenities` - Amenities preview
- `.resbs-quickview-map` - Map preview

### Buttons
- `.resbs-quickview-btn` - Quick view trigger button
- `.resbs-btn-primary` - Primary action button
- `.resbs-btn-secondary` - Secondary action button
- `.resbs-btn-outline` - Outline style button

## JavaScript API

### QuickViewManager Object
The main JavaScript object that handles all quick view functionality:

```javascript
// Properties
QuickViewManager.modal          // jQuery modal element
QuickViewManager.isOpen         // Boolean open state
QuickViewManager.currentPropertyId // Current property ID
QuickViewManager.isLoading      // Boolean loading state

// Methods
QuickViewManager.openQuickView(propertyId)     // Open quick view
QuickViewManager.closeQuickView()              // Close quick view
QuickViewManager.loadQuickViewContent(id)      // Load content via AJAX
QuickViewManager.switchMainImage($thumb)       // Switch gallery image
QuickViewManager.handleBookNow(propertyId)     // Handle booking
QuickViewManager.handleFavorite(id, $button)   // Handle favorites
```

### Events
The system triggers custom events for integration:

```javascript
// Quick view opened
$(document).on('resbs:quickview:opened', function(e, propertyId) {
    // Custom logic when quick view opens
});

// Quick view closed
$(document).on('resbs:quickview:closed', function(e, propertyId) {
    // Custom logic when quick view closes
});

// Content loaded
$(document).on('resbs:quickview:loaded', function(e, propertyId, content) {
    // Custom logic when content is loaded
});
```

## Keyboard Navigation

### Supported Keys
- **Escape** - Close modal
- **Arrow Left** - Previous gallery image
- **Arrow Right** - Next gallery image
- **Tab** - Navigate through interactive elements

### Focus Management
- Modal automatically focuses on close button when opened
- Focus returns to trigger button when closed
- Full keyboard navigation support

## Responsive Design

### Breakpoints
- **Desktop** (> 768px) - Side-by-side layout
- **Tablet** (≤ 768px) - Stacked layout
- **Mobile** (≤ 480px) - Optimized mobile layout

### Mobile Optimizations
- Touch-friendly button sizes
- Swipe gestures for gallery navigation
- Optimized modal sizing
- Reduced animations for performance

## Accessibility Features

### ARIA Support
- `role="dialog"` on modal
- `aria-labelledby` for modal title
- `aria-hidden` for modal state
- Proper focus management

### Screen Reader Support
- Semantic HTML structure
- Alt text for all images
- Descriptive button labels
- Status announcements

### Keyboard Navigation
- Full keyboard accessibility
- Logical tab order
- Escape key to close
- Arrow keys for gallery

## Performance Optimizations

### Lazy Loading
- Images load only when needed
- Intersection Observer for lazy loading
- Efficient DOM manipulation

### Caching
- AJAX responses can be cached
- Minimal DOM queries
- Event delegation for efficiency

### Mobile Performance
- Reduced animations on mobile
- Optimized image sizes
- Efficient touch handling

## Security Features

### Input Validation
- Property ID validation
- Nonce verification for AJAX requests
- Sanitized output for all content

### Output Escaping
- All text escaped with `esc_html()`
- URLs escaped with `esc_url()`
- Attributes escaped with `esc_attr()`

### XSS Protection
- No direct HTML injection
- Sanitized user inputs
- Secure AJAX endpoints

## Translation Support

### Text Domain
All text uses the `realestate-booking-suite` text domain.

### Translatable Strings
- Button labels
- Loading messages
- Error messages
- UI text elements

### Translation Functions
- `esc_html_e()` for display text
- `esc_attr_e()` for attributes
- `esc_html__()` for return values

## Customization

### CSS Customization
Override styles in your theme:

```css
/* Custom modal styling */
.resbs-quickview-modal {
    z-index: 9999999; /* Higher z-index */
}

/* Custom button styling */
.resbs-quickview-btn {
    background: #your-color;
    color: #your-text-color;
}

/* Custom gallery styling */
.resbs-quickview-gallery {
    border-radius: 12px;
}
```

### JavaScript Customization
Extend functionality with custom code:

```javascript
// Custom quick view handler
$(document).on('click', '.custom-quickview-trigger', function() {
    var propertyId = $(this).data('property-id');
    RESBSQuickView.openQuickView(propertyId);
});

// Custom content modification
$(document).on('resbs:quickview:loaded', function(e, propertyId, content) {
    // Modify content before display
    $('.resbs-quickview-wrapper').find('.custom-element').addClass('highlighted');
});
```

## Browser Support

### Modern Browsers
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

### Legacy Support
- Internet Explorer 11 (with polyfills)
- Older mobile browsers

### Feature Detection
- Intersection Observer for lazy loading
- CSS Grid for layout
- Flexbox for alignment

## Troubleshooting

### Common Issues

#### Quick View Not Opening
1. Check JavaScript console for errors
2. Verify AJAX URL is correct
3. Ensure nonce is valid
4. Check property ID exists

#### Content Not Loading
1. Verify AJAX endpoint is working
2. Check property post type and status
3. Ensure proper permissions
4. Check for PHP errors

#### Styling Issues
1. Check for CSS conflicts
2. Verify CSS file is loading
3. Check responsive breakpoints
4. Clear browser cache

#### Mobile Issues
1. Check touch event handling
2. Verify responsive CSS
3. Test on actual devices
4. Check viewport settings

### Debug Mode
Enable debug mode for troubleshooting:

```javascript
// Enable debug logging
window.RESBS_DEBUG = true;
```

## Performance Monitoring

### Metrics to Track
- Modal open/close times
- AJAX response times
- Image loading performance
- User interaction rates

### Optimization Tips
- Use image optimization
- Implement caching strategies
- Monitor bundle sizes
- Test on slow connections

## Future Enhancements

### Planned Features
- Video support in gallery
- Advanced map integration
- Social sharing buttons
- Print functionality
- Comparison mode

### API Extensions
- Custom content hooks
- Advanced filtering
- Analytics integration
- A/B testing support

## Examples

### Basic Implementation
```php
// Add quick view to custom template
if (function_exists('do_action')) {
    do_action('resbs_property_card_after_content', get_the_ID(), 'custom');
}
```

### Advanced Customization
```javascript
// Custom quick view with additional data
$(document).on('click', '.custom-property-card', function() {
    var propertyId = $(this).data('property-id');
    var customData = $(this).data('custom-info');
    
    // Store custom data
    window.customPropertyData = customData;
    
    // Open quick view
    RESBSQuickView.openQuickView(propertyId);
});
```

### Theme Integration
```css
/* Theme-specific quick view styling */
.resbs-quickview-modal {
    font-family: 'Your Theme Font', sans-serif;
}

.resbs-quickview-wrapper {
    border: 2px solid #your-theme-color;
    box-shadow: 0 0 20px rgba(your-theme-color, 0.3);
}
```

This Quick View system provides a comprehensive solution for property previews that enhances user experience while maintaining high performance and accessibility standards.
