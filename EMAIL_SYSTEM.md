# Email System Documentation

## Overview

The Email System provides comprehensive email functionality for the RealEstate Booking Suite plugin, including customizable templates, multilingual support, and automated notifications for property submissions, booking confirmations, and search alerts.

## Core Features

### 1. **Email Types**
- **Property Submission Emails**: Notify users when they submit properties
- **Booking Confirmation Emails**: Confirm booking requests
- **Booking Cancellation Emails**: Notify about booking cancellations
- **Search Alert Emails**: Send alerts when new properties match saved searches

### 2. **Customizable Templates**
- HTML and plain text email support
- Dynamic placeholder system
- Template editor with preview functionality
- Default templates with translation support

### 3. **SMTP Configuration**
- Custom SMTP server settings
- Multiple encryption options (TLS, SSL)
- Authentication support
- Test email functionality

### 4. **Search Alerts System**
- Automated property matching
- Configurable alert frequencies (daily, weekly)
- Email-based alert management
- Database-driven alert storage

## Installation & Setup

### 1. **Automatic Integration**
The email system is automatically integrated and includes:
- Email Manager class
- Search Alerts Manager class
- Admin settings page
- Template editor
- SMTP configuration

### 2. **Admin Configuration**
1. Go to **Properties > Email Settings**
2. Configure general email settings
3. Set up SMTP (optional)
4. Customize email templates
5. Test email functionality

## Email Settings

### 1. **General Settings**
- **From Name**: Name displayed in email "From" field
- **From Email**: Email address for outgoing emails
- **Reply-To Email**: Email address for replies
- **HTML Emails**: Enable/disable HTML email format

### 2. **Email Types**
- **Property Submission**: Enable emails for new property submissions
- **Booking Emails**: Enable booking confirmation/cancellation emails
- **Search Alerts**: Enable saved search alert emails
- **Admin Notifications**: Send admin notifications for new submissions

### 3. **SMTP Settings**
- **Enable SMTP**: Use custom SMTP server
- **SMTP Host**: Server hostname (e.g., smtp.gmail.com)
- **SMTP Port**: Server port (usually 587 for TLS, 465 for SSL)
- **SMTP Username**: Authentication username
- **SMTP Password**: Authentication password
- **Encryption**: TLS, SSL, or None

## Email Templates

### 1. **Template Types**

#### Property Submission Email
**Default Subject**: `Property Submission Received - {site_name}`

**Available Placeholders**:
- `{site_name}` - Website name
- `{property_title}` - Property title
- `{property_url}` - Property permalink
- `{property_id}` - Property ID
- `{submission_date}` - Submission date
- `{submission_time}` - Submission time
- `{submitter_name}` - Submitter's name
- `{submitter_email}` - Submitter's email
- `{submitter_phone}` - Submitter's phone

#### Booking Confirmation Email
**Default Subject**: `Booking Confirmed - {property_title}`

**Available Placeholders**:
- `{site_name}` - Website name
- `{booking_id}` - Booking ID
- `{booking_date}` - Booking date
- `{booking_time}` - Booking time
- `{property_title}` - Property title
- `{property_url}` - Property permalink
- `{customer_name}` - Customer name
- `{customer_email}` - Customer email
- `{customer_phone}` - Customer phone
- `{booking_notes}` - Booking notes

#### Booking Cancellation Email
**Default Subject**: `Booking Cancelled - {property_title}`

**Available Placeholders**:
- `{site_name}` - Website name
- `{booking_id}` - Booking ID
- `{booking_date}` - Booking date
- `{booking_time}` - Booking time
- `{property_title}` - Property title
- `{property_url}` - Property permalink
- `{customer_name}` - Customer name
- `{customer_email}` - Customer email
- `{customer_phone}` - Customer phone

#### Search Alert Email
**Default Subject**: `New Properties Found - {site_name}`

**Available Placeholders**:
- `{site_name}` - Website name
- `{search_id}` - Search alert ID
- `{search_criteria}` - Search criteria description
- `{properties_count}` - Number of matching properties
- `{subscriber_name}` - Subscriber name
- `{subscriber_email}` - Subscriber email
- `{alert_date}` - Alert date

### 2. **Template Editor**
- **Visual Editor**: Rich text editing with placeholder insertion
- **Preview Function**: Preview emails with sample data
- **Placeholder Helper**: Easy insertion of dynamic content
- **Auto-save**: Automatic saving of template changes

### 3. **HTML Email Styling**
- **Responsive Design**: Mobile-friendly email layouts
- **Dark Mode Support**: Automatic dark mode adaptation
- **Print Styles**: Optimized for printing
- **Cross-client Compatibility**: Works across email clients

## Search Alerts System

### 1. **Alert Creation**
Users can create search alerts by:
1. Using property search filters
2. Filling out the search alert form
3. Selecting alert frequency
4. Providing contact information

### 2. **Alert Management**
- **Email-based Management**: Alerts tied to email addresses
- **Frequency Control**: Daily or weekly alerts
- **Automatic Matching**: System finds matching properties
- **Alert Deletion**: Users can delete their alerts

### 3. **Alert Processing**
- **Cron Job**: Automated processing every hour
- **Property Matching**: Advanced query matching
- **New Properties Only**: Only alerts for properties created in last 24 hours
- **Email Sending**: Automatic email dispatch

### 4. **Database Structure**
```sql
CREATE TABLE wp_resbs_search_alerts (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    name varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    search_criteria longtext NOT NULL,
    frequency varchar(20) DEFAULT 'daily',
    last_sent datetime DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    status varchar(20) DEFAULT 'active',
    user_id bigint(20) DEFAULT NULL,
    PRIMARY KEY (id)
);
```

## Usage Examples

### 1. **Property Submission Email**
```php
// Trigger property submission email
do_action('resbs_property_submitted', $property_id, array(
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '+1-555-123-4567'
));
```

### 2. **Booking Confirmation Email**
```php
// Trigger booking confirmation email
do_action('resbs_booking_confirmed', $booking_id, array(
    'name' => 'Jane Smith',
    'email' => 'jane@example.com',
    'phone' => '+1-555-987-6543',
    'date' => '2024-01-15',
    'time' => '2:00 PM',
    'property_title' => 'Beautiful Family Home',
    'property_url' => 'https://example.com/property/123',
    'notes' => 'Please call before arrival'
));
```

### 3. **Search Alert Email**
```php
// Trigger search alert email
do_action('resbs_search_alert_triggered', $search_id, array(
    'name' => 'Bob Johnson',
    'email' => 'bob@example.com',
    'criteria' => '3 bedrooms, 2 bathrooms, under $500,000'
), $matching_properties);
```

### 4. **Shortcode Usage**
```php
// Display search alerts form
[resbs_search_alerts]

// Display search alerts form with email pre-filled
[resbs_search_alerts email="user@example.com"]

// Display only the form (no alerts list)
[resbs_search_alerts show_list="false"]

// Display only the alerts list
[resbs_search_alerts show_form="false" email="user@example.com"]
```

## API Reference

### 1. **Email Manager Methods**

#### `send_email($to, $subject, $message)`
Send an email with proper headers and formatting.

#### `replace_placeholders($content, $template_type, $object_id, $data)`
Replace placeholders in email content with actual data.

#### `wrap_html_email($content)`
Wrap email content in HTML template with styling.

#### `get_placeholders($template_type, $object_id, $data)`
Get available placeholders for a specific template type.

### 2. **Search Alerts Manager Methods**

#### `save_search_alert($name, $email, $search_criteria, $frequency)`
Save a new search alert to the database.

#### `delete_search_alert($alert_id)`
Delete a search alert from the database.

#### `get_search_alerts_by_email($email)`
Get all search alerts for a specific email address.

#### `find_matching_properties($criteria)`
Find properties that match the given search criteria.

#### `process_search_alert($alert)`
Process a single search alert and send email if matches found.

### 3. **AJAX Endpoints**

#### `resbs_send_test_email`
Send a test email to verify email settings.

#### `resbs_preview_email_template`
Preview email template with sample data.

#### `resbs_save_search_alert`
Save a new search alert.

#### `resbs_delete_search_alert`
Delete an existing search alert.

#### `resbs_get_search_alerts`
Get search alerts for an email address.

## Security Features

### 1. **Input Validation**
- All inputs sanitized using WordPress functions
- Email address validation
- Required field validation
- XSS protection

### 2. **Rate Limiting**
- Search alert creation: 5 per 5 minutes
- Search alert deletion: 10 per 5 minutes
- Search alert retrieval: 20 per 5 minutes
- Test email sending: 3 per 5 minutes

### 3. **Nonce Protection**
- All AJAX requests protected with nonces
- Form submissions verified
- CSRF attack prevention

### 4. **Capability Checks**
- Admin functions require `manage_options` capability
- User-specific operations check ownership
- Proper permission validation

## Translation Support

### 1. **Translation-Ready**
All user-facing text is wrapped in translation functions:
- `esc_html_e()` for displayed text
- `esc_attr_e()` for HTML attributes
- `__()` for dynamic text

### 2. **Translation Files**
Translation files can be created for:
- Email templates and subjects
- Admin interface text
- Error messages and notifications
- Form labels and buttons

### 3. **RTL Support**
CSS includes RTL (Right-to-Left) support for Arabic, Hebrew, and other RTL languages.

## Customization

### 1. **Template Customization**
```php
// Filter email template
add_filter('resbs_email_template_property_submission', function($template) {
    return 'Your custom template here...';
});

// Filter email subject
add_filter('resbs_email_subject_property_submission', function($subject) {
    return 'Custom Subject: {property_title}';
});
```

### 2. **Email Styling**
```css
/* Custom email styles */
.resbs-email-container {
    max-width: 600px;
    font-family: 'Your Font', Arial, sans-serif;
}

.resbs-email-header {
    background: #your-color;
}
```

### 3. **Search Criteria**
```php
// Add custom search criteria
add_filter('resbs_search_criteria_fields', function($fields) {
    $fields['custom_field'] = 'Custom Field';
    return $fields;
});
```

## Troubleshooting

### 1. **Common Issues**

#### Emails Not Sending
- Check SMTP settings
- Verify email addresses
- Check server logs
- Test with different email providers

#### Search Alerts Not Working
- Verify cron jobs are running
- Check database table exists
- Review search criteria format
- Check email template settings

#### Template Issues
- Verify placeholder syntax
- Check HTML formatting
- Test with preview function
- Validate email content

### 2. **Debug Mode**
Enable debug mode for troubleshooting:

```php
// Add to wp-config.php
define('RESBS_EMAIL_DEBUG', true);
```

### 3. **Logging**
Email sending is logged to WordPress error log:
- Successful sends
- Failed sends
- Error details
- SMTP connection issues

## Performance Optimization

### 1. **Email Queue**
- Batch email processing
- Rate limiting for SMTP
- Efficient database queries
- Memory management

### 2. **Search Alerts**
- Optimized property queries
- Indexed database fields
- Cached search results
- Efficient cron processing

### 3. **Template Caching**
- Cached template rendering
- Optimized placeholder replacement
- Reduced database queries
- Memory-efficient processing

## Future Enhancements

### 1. **Planned Features**
- Email queue system
- Advanced template editor
- Email analytics
- A/B testing for templates

### 2. **Integration Opportunities**
- Third-party email services
- Advanced search filters
- User preference management
- Email marketing integration

## Support

For technical support and feature requests:
1. Check the documentation
2. Review troubleshooting section
3. Contact plugin support
4. Submit feature requests

## Changelog

### Version 1.0.0
- Initial release
- Basic email functionality
- Property submission emails
- Booking confirmation/cancellation emails
- Search alerts system
- SMTP configuration
- Template editor
- Translation-ready
- Security features implemented
- Responsive design
- Accessibility support
