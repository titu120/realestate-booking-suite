# LIVE SITE TROUBLESHOOTING GUIDE

## Quick Diagnostic Test

1. **Go to your saved-properties page on live site**
2. **Add `?resbs_debug_css=1` to the URL** (must be logged in as admin)
   - Example: `https://yoursite.com/saved-properties/?resbs_debug_css=1`
3. **Check the diagnostic box** that appears at the bottom of the page
4. **Look for:**
   - Are CSS files showing as "EXISTS: YES"?
   - Are CSS files showing as "READABLE: YES"?
   - Is "resbs-shortcodes" in the "Enqueued Styles" list?
   - What version numbers are shown?

## Common Issues and Solutions

### Issue 1: CSS File Not Found (EXISTS: NO)

**Problem:** The CSS file doesn't exist on the server.

**Solution:**
1. Check if file exists: `wp-content/plugins/realestate-booking-suite/assets/css/shortcodes.css`
2. Verify file was uploaded correctly
3. Check file permissions (should be 644)
4. Re-upload the file via FTP/SFTP

### Issue 2: CSS File Not Readable (READABLE: NO)

**Problem:** File exists but server can't read it.

**Solution:**
1. Check file permissions: `chmod 644 shortcodes.css`
2. Check folder permissions: `chmod 755 assets/css/`
3. Check if file ownership is correct

### Issue 3: CSS Not Enqueued (Not in Enqueued Styles list)

**Problem:** WordPress isn't loading the CSS file.

**Possible Causes:**
1. **Page detection failing:**
   - Check if page slug is exactly "saved-properties"
   - Check if shortcode `[resbs_favorites]` is in page content

2. **Plugin conflict:**
   - Temporarily deactivate other plugins
   - Test if CSS loads

3. **Theme conflict:**
   - Switch to default theme temporarily
   - Test if CSS loads

4. **Caching plugin:**
   - Clear all caches
   - Temporarily disable caching plugin
   - Test if CSS loads

### Issue 4: CSS Loads But Styles Don't Apply

**Problem:** CSS file loads but styles are overridden.

**Solution:**
1. Check browser DevTools → Elements tab
2. Inspect the property grid element
3. Look for conflicting CSS rules
4. Check if `!important` flags are present
5. Verify CSS specificity is high enough

## Step-by-Step Debugging

### Step 1: Verify File Exists
```bash
# Via FTP/SFTP, check:
wp-content/plugins/realestate-booking-suite/assets/css/shortcodes.css
```

### Step 2: Check File Permissions
```bash
# Via SSH (if available):
cd wp-content/plugins/realestate-booking-suite/assets/css/
ls -la shortcodes.css
# Should show: -rw-r--r-- (644)
```

### Step 3: Test Direct Access
Open in browser:
```
https://yoursite.com/wp-content/plugins/realestate-booking-suite/assets/css/shortcodes.css
```
- If you see CSS code: ✅ File is accessible
- If you see 404: ❌ File path is wrong or file doesn't exist
- If you see blank/error: ❌ File permissions issue

### Step 4: Check Browser Console
1. Open saved-properties page
2. Press F12 (DevTools)
3. Go to **Console** tab
4. Look for red errors
5. Go to **Network** tab
6. Filter: **CSS**
7. Look for `shortcodes.css`
8. Check:
   - Status: Should be 200 (not 404)
   - Size: Should be > 0
   - Type: Should be `text/css`

### Step 5: Check WordPress Debug
Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Then check: `wp-content/debug.log` for errors.

## Force CSS to Load (Emergency Fix)

If nothing works, add this to your theme's `functions.php` (temporary):

```php
add_action('wp_enqueue_scripts', function() {
    if (is_page('saved-properties') || (is_page() && has_shortcode(get_post()->post_content, 'resbs_favorites'))) {
        wp_enqueue_style(
            'resbs-shortcodes-force',
            plugin_dir_url(__FILE__) . '../plugins/realestate-booking-suite/assets/css/shortcodes.css',
            array(),
            time() // Force reload every time
        );
    }
}, 999);
```

## Still Not Working?

1. **Check diagnostic output** (`?resbs_debug_css=1`)
2. **Check browser console** for errors
3. **Check server error logs**
4. **Verify plugin is activated**
5. **Check if other plugin CSS files load** (archive.css, style.css, etc.)
6. **Compare local vs live:**
   - File paths
   - WordPress version
   - PHP version
   - Server configuration

## Contact Information

If you need help, provide:
1. Diagnostic output (from `?resbs_debug_css=1`)
2. Browser console errors
3. Server error logs
4. WordPress/PHP versions
5. List of active plugins

