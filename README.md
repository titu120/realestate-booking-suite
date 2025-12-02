# RealEstate Booking Suite

A professional WordPress plugin for real estate property management, booking, and display. Includes Elementor widgets, advanced search, map integration, frontend dashboard, and more.

## Description

RealEstate Booking Suite is a comprehensive solution for real estate websites. It allows property owners, agents, and users to submit properties, manage bookings, display properties in responsive layouts, and interact with a full-featured frontend dashboard.

## Features

### Core Features
- **Property Management**: Custom post type for properties with extensive metadata
- **Frontend Property Submission**: Users and agents can submit properties from the frontend
- **Advanced Search**: AJAX-powered search with filters (price, location, type, status, etc.)
- **Map Integration**: Interactive maps with Leaflet.js and OpenStreetMap
- **Property Details Page**: Beautiful single property template with all details
- **Archive Templates**: Multiple archive layouts (grid, list, map view)
- **Booking Management**: Complete booking system with history and management
- **Favorites/Wishlist**: Save favorite properties
- **Search Alerts**: Email notifications for new matching properties
- **Contact Forms**: Property-specific contact forms
- **Email Notifications**: Automated email system for bookings and inquiries

### Elementor Integration
- Property Grid Widget
- Property Carousel/Slider Widget
- Search Widget
- Listings Widget
- Request Form Widget
- Authentication Widget

### Frontend Features
- **User Dashboard**: Complete frontend dashboard for users and agents
- **Property Management**: Users can manage their submitted properties
- **Booking History**: View and manage booking history
- **Profile Management**: Update user profiles
- **Saved Properties**: Favorites/wishlist functionality

### Admin Features
- **Admin Dashboard**: Statistics and analytics
- **Property Metabox**: Advanced property data management
- **Contact Messages**: Manage inquiries and messages
- **Email Management**: Configure email templates
- **Settings Panel**: Comprehensive settings page
- **Badge System**: Property badges (Featured, New, etc.)

### Technical Features
- **Responsive Design**: Mobile-first, fully responsive
- **Block Theme Compatible**: Works with WordPress block themes
- **Translation Ready**: Full i18n support with .pot file
- **AJAX Powered**: Smooth AJAX interactions throughout
- **Security**: Comprehensive security with nonces, sanitization, and escaping
- **Performance**: Optimized queries and caching

## Installation

### Requirements
- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

### Installation Steps

1. **Upload the Plugin**
   - Download the plugin zip file
   - Go to WordPress Admin → Plugins → Add New
   - Click "Upload Plugin" and select the zip file
   - Click "Install Now"

2. **Activate the Plugin**
   - After installation, click "Activate Plugin"

3. **Configure Settings**
   - Go to Settings → RealEstate Booking Suite
   - Configure general settings, email settings, and other options

4. **Create Required Pages** (Optional)
   - The plugin will automatically create a "Saved Properties" page
   - The plugin will automatically create a "Profile" page
   - You can customize these pages as needed

5. **Set Permalinks**
   - Go to Settings → Permalinks
   - Click "Save Changes" to flush rewrite rules

## Configuration

### Initial Setup

1. **Property Settings**
   - Configure property types, statuses, and locations
   - Set up property fields and metadata

2. **Email Configuration**
   - Configure SMTP settings (recommended)
   - Set up email templates
   - Test email functionality

3. **Map Settings**
   - Configure map provider (OpenStreetMap is default)
   - Set default map location
   - Configure map markers

4. **User Roles**
   - Configure user roles and capabilities
   - Set up agent permissions

### Elementor Setup

1. **Install Elementor** (if not already installed)
2. **Add Widgets**
   - Drag and drop RealEstate widgets into your pages
   - Configure widget settings
   - Style as needed

## Usage

### For Site Administrators

1. **Add Properties**
   - Go to Properties → Add New
   - Fill in property details
   - Add images and media
   - Publish

2. **Manage Bookings**
   - View bookings in the dashboard
   - Approve/reject booking requests
   - Manage booking status

3. **View Statistics**
   - Check dashboard for property statistics
   - View booking analytics
   - Monitor user activity

### For Property Agents

1. **Submit Properties**
   - Log in to your account
   - Go to Dashboard → Submit Property
   - Fill in property details
   - Submit for review

2. **Manage Properties**
   - View your submitted properties
   - Edit property details
   - Update property status

3. **View Bookings**
   - Check booking requests
   - Respond to inquiries
   - Manage bookings

### For Visitors

1. **Browse Properties**
   - Use the archive page to browse all properties
   - Use search and filters to find properties
   - View property details

2. **Save Favorites**
   - Click the heart icon on property cards
   - View saved properties in "My Saved Properties"

3. **Contact Agents**
   - Use contact forms on property pages
   - Send booking requests
   - Receive email confirmations

## Shortcodes

- `[resbs_property_archive]` - Display property archive
- `[resbs_property_search]` - Display search form
- `[resbs_favorites]` - Display saved properties
- `[resbs_user_dashboard]` - Display user dashboard
- `[resbs_submit_property]` - Display property submission form

## Hooks & Filters

The plugin provides various hooks and filters for developers. See the documentation for a complete list.

## Troubleshooting

### Common Issues

**Properties not displaying:**
- Check permalink settings (Settings → Permalinks → Save Changes)
- Verify properties are published
- Check template settings

**Maps not loading:**
- Verify internet connection (uses OpenStreetMap)
- Check browser console for errors
- Verify Leaflet.js is loading

**Emails not sending:**
- Configure SMTP settings
- Check spam folder
- Verify email configuration in settings

**Elementor widgets not showing:**
- Ensure Elementor is installed and activated
- Clear Elementor cache
- Regenerate CSS in Elementor

## Support

For support, feature requests, or bug reports, please contact:
- Author: Softivus
- Website: https://softivus.com

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## Credits

- **Leaflet.js**: Open-source JavaScript library for mobile-friendly interactive maps
- **Font Awesome**: Icon library
- **OpenStreetMap**: Map tiles provider

## License

This plugin is licensed under the GPL-2.0 or later license.

## Copyright

© 2024 Softivus. All rights reserved.

