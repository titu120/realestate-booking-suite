CACHE BUSTING FIX FOR LIVE SITE
================================

PROBLEM:
CSS changes not appearing on live site even after fresh plugin installation.

SOLUTION IMPLEMENTED:
1. Added cache busting function (resbs_get_css_version) that uses file modification time
2. Updated all CSS enqueue calls to use dynamic version numbers
3. CSS files now automatically get new version numbers when modified

HOW TO FIX CACHE ISSUES ON LIVE SITE:
=====================================

1. CLEAR ALL CACHES:
   - WordPress cache (if using caching plugin like WP Super Cache, W3 Total Cache, etc.)
   - Browser cache (Ctrl+Shift+Delete or hard refresh: Ctrl+F5)
   - Server/CDN cache (if using Cloudflare, etc.)
   - Object cache (Redis, Memcached)

2. DISABLE CACHING PLUGINS TEMPORARILY:
   - Go to WordPress Admin > Plugins
   - Deactivate caching plugins temporarily
   - Test if CSS loads correctly
   - If it works, reconfigure caching plugin to exclude CSS files or use proper cache busting

3. CHECK FILE PERMISSIONS:
   - Ensure CSS files are readable (644 permissions)
   - Ensure plugin directory has correct permissions

4. VERIFY CSS FILES ARE UPLOADED:
   - Check that shortcodes.css exists in: wp-content/plugins/realestate-booking-suite/assets/css/
   - Verify file modification dates are recent

5. CHECK FOR MINIFICATION/OPTIMIZATION PLUGINS:
   - Plugins like Autoptimize, WP Rocket, etc. may be combining/minifying CSS
   - Temporarily disable to test
   - If needed, exclude shortcodes.css from optimization

6. FORCE CSS RELOAD:
   - Add ?v=timestamp to CSS URL in browser DevTools
   - Or use browser hard refresh (Ctrl+F5 / Cmd+Shift+R)

7. CHECK BROWSER CONSOLE:
   - Open DevTools (F12)
   - Check Network tab for CSS file loading
   - Verify CSS file is loading with correct version number
   - Check for 404 errors or blocked resources

TECHNICAL DETAILS:
==================
- Cache busting function: resbs_get_css_version()
- Uses filemtime() to get file modification timestamp
- Version number changes automatically when CSS file is modified
- All CSS enqueue calls now use dynamic versions

FILES MODIFIED:
===============
- realestate-booking-suite.php (added cache busting function)
- includes/class-resbs-favorites.php (updated CSS enqueue)
- includes/class-resbs-shortcodes.php (updated CSS enqueue)

TESTING:
========
After clearing caches, check browser DevTools:
1. Network tab > Filter: CSS
2. Look for shortcodes.css
3. Check the version parameter in URL (should be timestamp, not "1.0.0")
4. Verify CSS rules are applied correctly

