<?php
/**
 * Debug Upload Issues
 * 
 * This file helps diagnose upload issues in the Real Estate Booking Suite plugin.
 * Access it via: yoursite.com/wp-content/plugins/realestate-booking-suite/debug-upload.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // Load WordPress
    require_once('../../../wp-load.php');
}

// Check if user has permission
if (!current_user_can('manage_options')) {
    die('Access denied. You must be an administrator to run this diagnostic.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Real Estate Booking Suite - Upload Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>Real Estate Booking Suite - Upload Debug</h1>
    
    <div class="section">
        <h2>WordPress Upload Settings</h2>
        <?php
        $upload_dir = wp_upload_dir();
        echo "<p><strong>Upload Directory:</strong> " . $upload_dir['basedir'] . "</p>";
        echo "<p><strong>Upload URL:</strong> " . $upload_dir['baseurl'] . "</p>";
        echo "<p><strong>Directory Writable:</strong> " . (is_writable($upload_dir['basedir']) ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</p>";
        ?>
    </div>
    
    <div class="section">
        <h2>PHP Upload Settings</h2>
        <?php
        echo "<p><strong>file_uploads:</strong> " . (ini_get('file_uploads') ? '<span class="success">Enabled</span>' : '<span class="error">Disabled</span>') . "</p>";
        echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
        echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
        echo "<p><strong>max_execution_time:</strong> " . ini_get('max_execution_time') . " seconds</p>";
        echo "<p><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</p>";
        ?>
    </div>
    
    <div class="section">
        <h2>Plugin Status</h2>
        <?php
        // Check if plugin is active
        if (is_plugin_active('realestate-booking-suite/realestate-booking-suite.php')) {
            echo "<p><span class='success'>Plugin is active</span></p>";
        } else {
            echo "<p><span class='error'>Plugin is not active</span></p>";
        }
        
        // Check if AJAX handlers are registered
        global $wp_filter;
        if (isset($wp_filter['wp_ajax_resbs_upload_property_media'])) {
            echo "<p><span class='success'>Upload AJAX handler is registered</span></p>";
        } else {
            echo "<p><span class='error'>Upload AJAX handler is NOT registered</span></p>";
        }
        
        if (isset($wp_filter['wp_ajax_resbs_delete_property_media'])) {
            echo "<p><span class='success'>Delete AJAX handler is registered</span></p>";
        } else {
            echo "<p><span class='error'>Delete AJAX handler is NOT registered</span></p>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>User Permissions</h2>
        <?php
        echo "<p><strong>Current User:</strong> " . wp_get_current_user()->user_login . "</p>";
        echo "<p><strong>Can Upload Files:</strong> " . (current_user_can('upload_files') ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</p>";
        echo "<p><strong>Can Edit Posts:</strong> " . (current_user_can('edit_posts') ? '<span class="success">Yes</span>' : '<span class="error">No</span>') . "</p>";
        ?>
    </div>
    
    <div class="section">
        <h2>JavaScript Test</h2>
        <p>Test if the JavaScript variables are properly localized:</p>
        <div id="js-test">
            <p>Loading JavaScript test...</p>
        </div>
        <script>
        jQuery(document).ready(function($) {
            var testHtml = '';
            
            if (typeof resbs_metabox !== 'undefined') {
                testHtml += '<p><span class="success">resbs_metabox object exists</span></p>';
                testHtml += '<p><strong>AJAX URL:</strong> ' + (resbs_metabox.ajax_url || 'Not set') + '</p>';
                testHtml += '<p><strong>Nonce:</strong> ' + (resbs_metabox.nonce || 'Not set') + '</p>';
            } else {
                testHtml += '<p><span class="error">resbs_metabox object is NOT defined</span></p>';
            }
            
            $('#js-test').html(testHtml);
        });
        </script>
    </div>
    
    <div class="section">
        <h2>Quick Fixes</h2>
        <p>If you're experiencing upload issues, try these solutions:</p>
        <ul>
            <li>Make sure the upload directory is writable: <code>chmod 755 <?php echo $upload_dir['basedir']; ?></code></li>
            <li>Increase PHP limits in your php.ini file</li>
            <li>Check that the plugin files are not corrupted</li>
            <li>Clear any caching plugins</li>
            <li>Check browser console for JavaScript errors</li>
        </ul>
    </div>
    
    <div class="section">
        <h2>Test Upload</h2>
        <form id="test-upload-form" enctype="multipart/form-data">
            <input type="file" id="test-file" name="test-file" accept="image/*">
            <button type="button" id="test-upload-btn">Test Upload</button>
        </form>
        <div id="upload-result"></div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-upload-btn').click(function() {
                var fileInput = document.getElementById('test-file');
                var file = fileInput.files[0];
                
                if (!file) {
                    alert('Please select a file first');
                    return;
                }
                
                var formData = new FormData();
                formData.append('files[0]', file);
                formData.append('action', 'resbs_upload_property_media');
                formData.append('nonce', resbs_metabox.nonce);
                
                $.ajax({
                    url: resbs_metabox.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#upload-result').html('<p class="success">Upload successful! Response: ' + JSON.stringify(response) + '</p>');
                    },
                    error: function(xhr, status, error) {
                        $('#upload-result').html('<p class="error">Upload failed: ' + error + '</p>');
                    }
                });
            });
        });
        </script>
    </div>
</body>
</html>
