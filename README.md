# RealEstate Booking Suite

Professional real estate booking plugin for WordPress that allows users and agents to submit properties, manage bookings, integrate with WooCommerce for payments, and display properties in responsive layouts.

## Description

RealEstate Booking Suite is a comprehensive WordPress plugin designed for real estate websites. It provides a complete solution for property management, booking systems, and property listings with advanced search capabilities, map integration, and seamless WooCommerce integration for payment processing.

## Features

### Core Features

- **Property Management**
  - Custom Post Type for properties
  - Property taxonomies (type, status, location, tags)
  - Comprehensive property meta fields
  - Frontend property submission form
  - Property editing (admin & frontend)
  - Property duplication
  - Property status management

- **User Management**
  - User registration system
  - Email verification
  - Custom user roles (Agent, Buyer, etc.)
  - Frontend dashboard
  - Profile management
  - Property ownership verification

- **Search & Filtering**
  - Advanced AJAX search functionality
  - Multiple filter options (price, bedrooms, bathrooms, area, etc.)
  - Property type/status filtering
  - Search alerts
  - Dynamic archive with filters

- **Display Features**
  - Property archive templates
  - Single property template
  - Property cards/grid layouts
  - Multiple layout options
  - Fully responsive design
  - Map integration (Google Maps / Mapbox)
  - Property badges (Featured, New, Sold, etc.)

- **WooCommerce Integration**
  - Booking system integration
  - Payment processing
  - Order management

- **Elementor Integration**
  - Multiple Elementor widgets
  - Property listings widget
  - Search widget
  - Property carousel
  - Request form widget
  - Authentication widget

- **Additional Features**
  - Favorites/Wishlist system
  - Contact forms
  - Email notifications
  - Search alerts
  - Quick view
  - Infinite scroll
  - Property badges
  - Booking management
  - Admin dashboard with statistics

## Installation

### Requirements

- WordPress 5.2 or higher
- PHP 7.1 or higher
- WooCommerce (optional, for payment processing)
- Elementor (optional, for page builder integration)

### Step-by-Step Installation

1. **Upload the Plugin**
   - Download the plugin zip file
   - Go to WordPress Admin → Plugins → Add New
   - Click "Upload Plugin"
   - Choose the zip file and click "Install Now"
   - Activate the plugin

2. **Initial Setup**
   - The plugin will automatically create necessary pages:
     - Saved Properties (Wishlist)
     - User Profile
     - Submit Property
   - Default property status terms will be created automatically

3. **Configure Settings**
   - Go to Properties → Settings
   - Configure general settings
   - Set up email templates
   - Configure map settings (Google Maps API key or Mapbox token)
   - Set up WooCommerce integration (if using)

4. **Configure Permalinks**
   - Go to Settings → Permalinks
   - Click "Save Changes" to flush rewrite rules

## Configuration

### Map Integration

#### Google Maps
1. Get a Google Maps API key from [Google Cloud Console](https://console.cloud.google.com/)
2. Enable Maps JavaScript API
3. Add the API key in Properties → Settings → Maps

#### Mapbox
1. Get a Mapbox access token from [Mapbox](https://www.mapbox.com/)
2. Add the token in Properties → Settings → Maps

### WooCommerce Integration

1. Install and activate WooCommerce
2. Go to Properties → Settings → WooCommerce
3. Enable booking integration
4. Configure booking products and pricing

### Email Settings

1. Go to Properties → Settings → Email
2. Configure email templates
3. Set up SMTP (recommended) for better deliverability
4. Test email sending

## Usage

### Adding Properties

#### From Admin Panel
1. Go to Properties → Add New
2. Fill in property details
3. Add property images
4. Set property location on map
5. Publish

#### From Frontend
1. Users can submit properties via the Submit Property page
2. Properties will be pending until approved by admin
3. Agents can manage their own properties from the frontend dashboard

### Displaying Properties

#### Using Shortcodes
- `[resbs_properties]` - Display property listings
- `[resbs_search]` - Display search form
- `[resbs_dashboard]` - Display user dashboard
- `[resbs_favorites]` - Display saved properties
- `[resbs_submit_property]` - Display property submission form

#### Using Elementor Widgets
1. Edit page with Elementor
2. Search for "RealEstate" widgets
3. Drag and drop desired widget
4. Configure widget settings

#### Using WordPress Widgets
1. Go to Appearance → Widgets
2. Add RealEstate widgets to widget areas
3. Configure widget settings

### Managing Bookings

1. Go to Properties → Bookings
2. View all booking requests
3. Update booking status
4. Manage bookings per property

## Shortcodes

### Property Listings
```
[resbs_properties]
[resbs_properties limit="10" type="house" status="for-sale"]
```

### Search Form
```
[resbs_search]
```

### User Dashboard
```
[resbs_dashboard]
[resbs_dashboard show_profile="yes"]
```

### Favorites
```
[resbs_favorites]
```

### Submit Property
```
[resbs_submit_property]
```

## Hooks & Filters

The plugin provides various hooks and filters for developers to extend functionality. Refer to the code documentation for available hooks.

## Security

- All user inputs are sanitized
- Nonce verification on all forms
- Capability checks for admin functions
- SQL injection protection via prepared statements
- XSS protection via output escaping
- File upload validation

## Translation

The plugin is fully translation-ready with text domain `realestate-booking-suite`. All strings are wrapped in translation functions.

To translate:
1. Use a translation plugin like Loco Translate
2. Or create translation files manually
3. Place .po/.mo files in `wp-content/languages/plugins/`

## Support

For support, feature requests, or bug reports, please contact:
- Author: Softivus
- Website: https://softivus.com

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed list of changes.

## Credits

- Font Awesome 6.4.0 for icons
- WordPress Core APIs
- WooCommerce (optional dependency)
- Elementor (optional dependency)

## License

This plugin is proprietary software. All rights reserved.

## Version

Current Version: 1.0.0

