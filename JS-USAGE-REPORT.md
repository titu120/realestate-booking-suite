# JavaScript Files Usage Report
## RealEstate Booking Suite Plugin

### ✅ JavaScript Files Currently IN USE (18 files)

#### Main Plugin Enqueued:
1. **dynamic-archive.js** - Enqueued in `realestate-booking-suite.php` (line 402-408)

#### Feature-Specific Enqueued:
2. **archive.js** - Enqueued in `class-resbs-archive-handler.php` and `class-resbs-favorites.php`
3. **badge-admin.js** - Enqueued in `class-resbs-badges.php` (admin only)
4. **quickview.js** - Enqueued in `class-resbs-quickview.php`
5. **favorites.js** - Enqueued in `class-resbs-favorites.php`
6. **maps.js** - Enqueued in `class-resbs-maps.php`
7. **property-metabox.js** - Enqueued in `class-resbs-property-metabox.php` (admin)
8. **elementor.js** - Enqueued in `class-resbs-elementor.php`
9. **shortcodes.js** - Enqueued in `class-resbs-shortcodes.php` (conditional)
10. **simple-archive.js** - Enqueued in `class-resbs-simple-archive.php`
11. **search-alerts.js** - Enqueued in `class-resbs-search-alerts.php`
12. **widgets.js** - Enqueued in `class-resbs-widgets.php`
13. **infinite-scroll.js** - Enqueued in `class-resbs-infinite-scroll.php`

#### Admin-Only Enqueued:
14. **admin-dashboard.js** - Enqueued in `class-resbs-admin-dashboard.php` (admin)
15. **email-admin.js** - Enqueued in `class-resbs-email-manager.php` (admin)

#### Directly Loaded in Templates:
16. **single-property-template.js** - Loaded directly in `templates/single-property.php` (line 494)
17. **single-property.js** - Loaded directly in `templates/single-property.php` (line 2174)

#### Used via Multiple Classes:
18. **main.js** - Enqueued in:
   - `class-resbs-frontend.php` (as 'resbs-frontend')
   - `class-resbs-search.php` (as 'resbs-search')
   - `templates/property-grid.php` (as 'resbs-property-grid')

---

### ⚠️ JavaScript Files with ISSUES (2 files)

1. **layouts.js** - **PROBLEM: Referenced but NOT enqueued**
   - Referenced in `class-resbs-shortcodes.php` line 275: `wp_enqueue_script('resbs-layouts')`
   - But `layouts.js` is commented out in `realestate-booking-suite.php` (lines 422-429)
   - The script handle 'resbs-layouts' is never registered
   - **Result**: WordPress will try to enqueue a non-existent script, causing errors

2. **forms.js** - **PROBLEM: Referenced but FILE DOES NOT EXIST**
   - Referenced in `class-resbs-shortcodes.php` line 324: `wp_enqueue_script('resbs-forms')`
   - But `forms.js` file does not exist in the plugin
   - The script handle 'resbs-forms' is never registered
   - **Result**: WordPress will try to enqueue a non-existent script, causing errors

---

### ❌ JavaScript Files NOT Currently Used (5 files)

1. **admin.js** - Contains admin tab functionality but is NOT enqueued anywhere
   - Has tab switching code for `.resbs-admin-tabs`
   - Comments in `property-metabox.php` mention "admin-tabs.js" handles tabs
   - Possibly replaced by `admin-tabs.js` or functionality moved elsewhere

2. **admin-tabs.js** - Contains comprehensive tab management but is NOT enqueued
   - Has `RESBS_TabManager` object
   - Referenced in comments in `property-metabox.js` (line 86)
   - But never actually enqueued in any PHP file

3. **main.js** - **PARTIALLY DISABLED**
   - Enqueued in multiple classes (frontend, search, property-grid)
   - But commented out in main plugin file (lines 414-420)
   - Currently used but may have conflicts

4. **shortcodes.js** - **PARTIALLY DISABLED**
   - Enqueued conditionally in `class-resbs-shortcodes.php`
   - But commented out in main plugin file (lines 431-438)
   - Currently used conditionally

5. **layouts.js** - **DISABLED**
   - Commented out in main plugin file (lines 422-429)
   - But still referenced in `class-resbs-shortcodes.php`
   - This causes errors when shortcode is used

---

### 🗑️ Disabled Files (2 files - already disabled)

1. **frontend-tabs.js.disabled** - Already disabled (has .disabled extension)
2. **simple-metabox.js.disabled** - Already disabled (has .disabled extension)

---

### 📊 Summary

- **Total JS files**: 23 (including 2 .disabled files)
- **Files in use**: 18 (78%)
- **Files with issues**: 2 (9%)
- **Files not used**: 5 (22%)

### 🐛 Critical Issues Found

1. **layouts.js** - Referenced but not registered/enqueued → Will cause JavaScript errors
2. **forms.js** - Referenced but file doesn't exist → Will cause JavaScript errors

### 💡 Recommendations

#### Immediate Fixes Needed:
1. **Fix layouts.js issue:**
   - Option A: Register and enqueue `layouts.js` properly
   - Option B: Remove the `wp_enqueue_script('resbs-layouts')` call from `class-resbs-shortcodes.php`

2. **Fix forms.js issue:**
   - Option A: Create `forms.js` file and register it
   - Option B: Remove the `wp_enqueue_script('resbs-forms')` call from `class-resbs-shortcodes.php`

#### Files to Consider Removing:
1. **admin.js** - Not enqueued, functionality may be in admin-tabs.js or elsewhere
2. **admin-tabs.js** - Not enqueued, but referenced in comments (check if needed)
3. **frontend-tabs.js.disabled** - Already disabled, can be deleted
4. **simple-metabox.js.disabled** - Already disabled, can be deleted

#### Files to Investigate:
1. **main.js** - Used in multiple places but commented out in main file (may have conflicts)
2. **shortcodes.js** - Used conditionally but commented out in main file (may have conflicts)

---

*Report generated by analyzing wp_enqueue_script calls and direct JS file references in the plugin codebase.*

