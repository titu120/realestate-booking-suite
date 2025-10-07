# Property Grid Widget Usage Guide

## Overview
The Property Grid Widget is a WordPress Appearance widget that displays properties in a responsive grid layout with advanced filtering capabilities. It can be placed in any theme sidebar or footer widget area.

## Features
- **Responsive Grid Layout**: 1-4 columns with automatic responsive adjustment
- **Advanced Filters**: Price range, location, and property type filtering
- **Customizable Display**: Show/hide price, meta info, excerpts, badges, and action buttons
- **Multiple Styles**: Default, Modern, Minimal, and Card styles
- **AJAX Filtering**: Real-time property filtering without page reload
- **Favorite System**: Users can add/remove properties from favorites
- **Translation Ready**: All labels are translation-ready
- **Security**: Proper escaping and sanitization of all inputs

## Installation
The widget is automatically registered when the plugin is activated. No additional installation steps are required.

## Usage

### Adding the Widget
1. Go to **Appearance > Widgets** in your WordPress admin
2. Find the **Property Grid** widget in the available widgets
3. Drag it to your desired widget area (sidebar, footer, etc.)
4. Configure the widget settings
5. Save the widget

### Widget Configuration

#### Basic Settings
- **Title**: Custom widget title (optional)
- **Number of Properties**: How many properties to display (1-20)
- **Columns**: Grid layout (1-4 columns)
- **Widget Style**: Choose from Default, Modern, Minimal, or Card styles

#### Filter Options
- **Show Filters**: Enable/disable the filter form
- **Show Price Filter**: Enable price range filtering
- **Show Location Filter**: Enable location-based filtering
- **Show Property Type Filter**: Enable property type filtering

#### Display Options
- **Show Price**: Display property prices
- **Show Meta Info**: Show bedrooms, bathrooms, and size
- **Show Excerpt**: Display property descriptions
- **Show Status Badges**: Show featured and status badges
- **Show Favorite Button**: Enable favorite functionality
- **Show Book Button**: Show booking action buttons

#### Advanced Settings
- **Order By**: Sort by date, title, price, or random
- **Order**: Ascending or descending
- **Filter by Property Type**: Pre-filter by specific property type
- **Filter by Property Status**: Pre-filter by specific status
- **Show Featured Properties Only**: Display only featured properties

## Filter Functionality

### Price Filter
Users can set minimum and maximum price ranges to filter properties. The filter works with the `_property_price` meta field.

### Location Filter
Filters properties by location using the `property_location` taxonomy. All available locations are automatically populated in the dropdown.

### Property Type Filter
Filters properties by type using the `property_type` taxonomy. All available property types are automatically populated in the dropdown.

## AJAX Features

### Real-time Filtering
When users change filter options, the widget automatically updates the property grid without reloading the page.

### Favorite System
Logged-in users can add/remove properties from their favorites list. The system stores favorites in user meta.

## Styling

### CSS Classes
The widget uses the following CSS classes for styling:
- `.resbs-property-grid-widget` - Main widget container
- `.resbs-widget-filters` - Filter form container
- `.resbs-property-grid` - Property grid container
- `.resbs-property-card` - Individual property card
- `.resbs-property-image` - Property image container
- `.resbs-property-content` - Property content container

### Style Variations
- `.resbs-style-default` - Default styling
- `.resbs-style-modern` - Modern styling with enhanced shadows
- `.resbs-style-minimal` - Minimal styling with borders
- `.resbs-style-card` - Card-style with borders

### Responsive Design
The widget automatically adjusts column layout based on screen size:
- Mobile (< 480px): 1 column
- Tablet (< 768px): 2 columns max
- Desktop (< 1024px): 3 columns max
- Large screens: Up to 4 columns

## Customization

### CSS Customization
You can customize the widget appearance by adding CSS to your theme's `style.css` file or using the WordPress Customizer.

### Translation
All text strings are translation-ready. Use a translation plugin like WPML or Polylang to translate the widget labels.

### Hooks and Filters
The widget provides several hooks for customization:
- `resbs_widget_property_query_args` - Modify the property query
- `resbs_widget_property_card_html` - Customize property card HTML
- `resbs_widget_filter_form_html` - Customize filter form HTML

## Troubleshooting

### Widget Not Appearing
1. Ensure the plugin is activated
2. Check that the `property` post type exists
3. Verify widget areas are registered in your theme

### Filters Not Working
1. Check that taxonomies (`property_type`, `property_location`, `property_status`) are registered
2. Ensure properties have the required meta fields
3. Check browser console for JavaScript errors

### Styling Issues
1. Clear any caching plugins
2. Check for CSS conflicts with your theme
3. Use browser developer tools to inspect elements

## Security Features
- All inputs are sanitized using WordPress functions
- All outputs are escaped using `esc_html()`, `esc_attr()`, etc.
- AJAX requests are protected with nonces
- User capabilities are checked for favorite functionality

## Browser Support
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Internet Explorer 11 (with polyfills)

## Performance
- Lazy loading for images
- Debounced resize events
- Efficient AJAX requests
- Minimal DOM manipulation
- CSS Grid for optimal layout performance
