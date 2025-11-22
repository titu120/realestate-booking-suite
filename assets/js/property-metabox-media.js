/**
 * Property Metabox Media Uploader JavaScript
 * Extracted from inline script in class-resbs-property-metabox.php
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        var galleryImages = [];
        
        // Agent Photo Uploader
        $('#upload-agent-photo').click(function(e) {
            e.preventDefault();
            
            var agentPhotoUploader = wp.media({
                title: 'Select Agent Photo',
                button: {
                    text: 'Use as Agent Photo'
                },
                multiple: false,
                library: {
                    type: 'image'
                }
            });
            
            agentPhotoUploader.on('select', function() {
                var attachment = agentPhotoUploader.state().get('selection').first().toJSON();
                
                // Update the hidden input
                $('#property_agent_photo').val(attachment.url);
                
                // Update the preview
                var agentPhotoUrl = attachment.url;
                $('#agent-photo-preview').html(
                    '<img src="' + agentPhotoUrl + '" alt="Agent Photo" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #0073aa;">'
                );
                
                // Show remove button
                if ($('#remove-agent-photo').length === 0) {
                    $('#upload-agent-photo').after('<button type="button" id="remove-agent-photo" class="button button-secondary" style="background: #dc3232; color: white; border: none;">Remove</button>');
                }
            });
            
            agentPhotoUploader.open();
        });
        
        // Remove Agent Photo
        $(document).on('click', '#remove-agent-photo', function(e) {
            e.preventDefault();
            
            // Clear the hidden input
            $('#property_agent_photo').val('');
            
            // Reset the preview
            $('#agent-photo-preview').html(
                '<div style="width: 60px; height: 60px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center; color: #666; font-size: 24px;">👤</div>'
            );
            
            // Hide remove button
            $('#remove-agent-photo').remove();
        });
        
        // WordPress Media Uploader
        $('#upload-gallery-button').click(function(e) {
            e.preventDefault();
            
            var mediaUploader = wp.media({
                title: 'Select Property Images',
                button: {
                    text: 'Add to Gallery'
                },
                multiple: true,
                library: {
                    type: 'image'
                }
            });
            
            mediaUploader.on('select', function() {
                var selection = mediaUploader.state().get('selection');
                
                selection.map(function(attachment) {
                    var attachmentData = attachment.toJSON();
                    galleryImages.push(attachmentData.id);
                });
                
                // Update hidden input
                $('#gallery-images').val(galleryImages.join(','));
                
                // Display selected images
                displayGalleryImages();
            });
            
            mediaUploader.open();
        });
        
        // Display selected images
        function displayGalleryImages() {
            var galleryList = $('#gallery-list');
            galleryList.empty();
            
            if (galleryImages.length === 0) {
                galleryList.html('<p style="color: #666; font-style: italic;">No images selected</p>');
                return;
            }
            
            galleryImages.forEach(function(imageId) {
                var imageUrl = wp.media.attachment(imageId).get('url');
                if (imageUrl) {
                    var imageHtml = '<div style="position: relative; display: inline-block; margin: 5px;">' +
                        '<img src="' + imageUrl + '" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px; border: 2px solid #0073aa;">' +
                        '<button type="button" class="remove-image" data-id="' + imageId + '" style="position: absolute; top: -5px; right: -5px; background: #ff0000; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer;">×</button>' +
                        '</div>';
                    galleryList.append(imageHtml);
                }
            });
        }
        
        // Remove image
        $(document).on('click', '.remove-image', function() {
            var imageId = $(this).data('id');
            galleryImages = galleryImages.filter(function(id) {
                return id != imageId;
            });
            $('#gallery-images').val(galleryImages.join(','));
            displayGalleryImages();
        });
    });
    
    // Function to refresh galleries
    function refreshGalleries() {
        // Get current post ID from URL
        var postId = window.location.search.match(/post=(\d+)/);
        if (!postId) return;
        postId = postId[1];
        
        // Make AJAX call to get updated gallery
        fetch(resbs_metabox.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=resbs_get_gallery&post_id=' + postId + '&nonce=' + resbs_metabox.nonce
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update gallery grid
                var galleryGrid = document.getElementById('gallery-grid');
                if (galleryGrid && data.data.gallery) {
                    galleryGrid.innerHTML = data.data.gallery;
                }
                
                // Update floor plans grid
                var floorPlansGrid = document.getElementById('floor-plans-grid');
                if (floorPlansGrid && data.data.floor_plans) {
                    floorPlansGrid.innerHTML = data.data.floor_plans;
                }
            }
        })
        .catch(error => {
            // Fallback to page reload
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        });
    }
    
    // Function to show image previews
    function showImagePreviews(files, inputName) {
        
        // Determine which gallery to use
        var galleryId = inputName.includes('gallery') ? 'gallery-grid' : 'floor-plans-grid';
        var gallery = document.getElementById(galleryId);
        
        if (!gallery) {
            console.error('Gallery not found:', galleryId);
            return;
        }
        
        // Clear existing previews
        var existingPreviews = gallery.querySelectorAll('.preview-item');
        existingPreviews.forEach(function(preview) {
            preview.remove();
        });
        
        // Add preview for each file
        Array.from(files).forEach(function(file, index) {
            if (file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    previewItem.style.cssText = 'display: inline-block; margin: 10px; border: 2px solid #0073aa; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: relative; animation: fadeIn 0.3s ease-in;';
                    
                    previewItem.innerHTML = '<img src="' + e.target.result + '" style="width: 150px; height: 150px; object-fit: cover; display: block;"><div style="position: absolute; top: 5px; right: 5px; background: rgba(0,115,170,0.9); color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px; font-weight: bold;">' + (index + 1) + '</div><div style="position: absolute; top: 5px; left: 5px; background: rgba(255,193,7,0.9); color: #000; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold;">PREVIEW</div><div style="padding: 8px; background: #f8f9fa; font-size: 12px; color: #666; text-align: center; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="' + file.name + '">' + file.name + '</div>';
                    
                    gallery.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Handle file inputs
    document.addEventListener('DOMContentLoaded', function() {
        // Add CSS animations
        var style = document.createElement('style');
        style.textContent = '@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } } @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } } @keyframes fadeIn { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } } .preview-item { transition: all 0.3s ease; } .preview-item:hover { transform: scale(1.05); box-shadow: 0 4px 16px rgba(0,0,0,0.2); }';
        document.head.appendChild(style);
        
        var fileInputs = document.querySelectorAll('input[type="file"]');
        
        fileInputs.forEach(function(input, index) {
            input.addEventListener('change', function(e) {
                var files = e.target.files;
                
                if (files.length > 0) {
                    
                    // Update the input area with visual feedback
                    var container = input.closest('div');
                    if (container) {
                        container.style.borderColor = '#28a745';
                        container.style.backgroundColor = '#f0fff4';
                        
                        // Add file count display
                        var existingCount = container.querySelector('.file-count');
                        if (existingCount) {
                            existingCount.remove();
                        }
                        
                        var countDiv = document.createElement('div');
                        countDiv.className = 'file-count';
                        countDiv.style.cssText = 'color: #28a745; font-weight: bold; margin-top: 10px; font-size: 14px;';
                        countDiv.textContent = '📁 ' + files.length + ' file(s) selected';
                        container.appendChild(countDiv);
                    }
                    
                    // Show preview images immediately
                    showImagePreviews(files, input.name);
                }
            });
        });
        
        // Check for upload success
        var successNotice = document.querySelector('.notice-success');
        if (successNotice && successNotice.textContent.includes('Images have been uploaded')) {
            document.querySelectorAll('.preview-item').forEach(function(preview) {
                preview.remove();
            });
        }
    });
    
    // Simple plus/minus button functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Handle plus/minus button clicks
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('resbs-number-btn')) {
                e.preventDefault();
                
                var button = e.target;
                var action = button.getAttribute('data-action');
                var targetId = button.getAttribute('data-target');
                var input = document.getElementById(targetId);
                
                if (input) {
                    var currentValue = parseInt(input.value) || 0;
                    var min = parseInt(input.getAttribute('min')) || 0;
                    var max = parseInt(input.getAttribute('max')) || 999;
                    
                    if (action === 'increase' && currentValue < max) {
                        input.value = currentValue + 1;
                    } else if (action === 'decrease' && currentValue > min) {
                        input.value = currentValue - 1;
                    }
                    
                    // Trigger change event
                    input.dispatchEvent(new Event('change'));
                }
            }
        });
        
        // FEATURES & AMENITIES FUNCTIONALITY
        initFeaturesAndAmenities();
        
        function initFeaturesAndAmenities() {
            // Load existing features and amenities
            loadExistingFeatures();
            loadExistingAmenities();
            
            // Setup feature suggestion clicks
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('resbs-suggestion-tag') && e.target.hasAttribute('data-feature')) {
                    addFeature(e.target.getAttribute('data-feature'));
                }
                if (e.target.classList.contains('resbs-suggestion-tag') && e.target.hasAttribute('data-amenity')) {
                    addAmenity(e.target.getAttribute('data-amenity'));
                }
            });
            
            // Setup custom feature input
            var featureInput = document.getElementById('property_features_input');
            var addFeatureBtn = document.getElementById('add-custom-feature');
            if (featureInput && addFeatureBtn) {
                addFeatureBtn.addEventListener('click', function() {
                    var customFeature = featureInput.value.trim();
                    if (customFeature) {
                        addFeature(customFeature);
                        featureInput.value = '';
                    }
                });
                
                featureInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        var customFeature = featureInput.value.trim();
                        if (customFeature) {
                            addFeature(customFeature);
                            featureInput.value = '';
                        }
                    }
                });
            }
            
            // Setup custom amenity input
            var amenityInput = document.getElementById('property_amenities_input');
            var addAmenityBtn = document.getElementById('add-custom-amenity');
            if (amenityInput && addAmenityBtn) {
                addAmenityBtn.addEventListener('click', function() {
                    var customAmenity = amenityInput.value.trim();
                    if (customAmenity) {
                        addAmenity(customAmenity);
                        amenityInput.value = '';
                    }
                });
                
                amenityInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        var customAmenity = amenityInput.value.trim();
                        if (customAmenity) {
                            addAmenity(customAmenity);
                            amenityInput.value = '';
                        }
                    }
                });
            }
        }
        
        function loadExistingFeatures() {
            var featuresField = document.getElementById('property_features');
            var container = document.getElementById('feature-tags-container');
            if (featuresField && container) {
                var features = featuresField.value ? featuresField.value.split(',') : [];
                container.innerHTML = '';
                features.forEach(function(feature) {
                    if (feature.trim()) {
                        addFeatureTag(feature.trim());
                    }
                });
            }
        }
        
        function loadExistingAmenities() {
            var amenitiesField = document.getElementById('property_amenities');
            var container = document.getElementById('amenity-tags-container');
            if (amenitiesField && container) {
                var amenities = amenitiesField.value ? amenitiesField.value.split(',') : [];
                container.innerHTML = '';
                amenities.forEach(function(amenity) {
                    if (amenity.trim()) {
                        addAmenityTag(amenity.trim());
                    }
                });
            }
        }
        
        function addFeature(feature) {
            var featuresField = document.getElementById('property_features');
            var container = document.getElementById('feature-tags-container');
            
            if (featuresField && container) {
                var currentFeatures = featuresField.value ? featuresField.value.split(',') : [];
                if (!currentFeatures.includes(feature)) {
                    currentFeatures.push(feature);
                    featuresField.value = currentFeatures.join(',');
                    addFeatureTag(feature);
                }
            }
        }
        
        function addAmenity(amenity) {
            var amenitiesField = document.getElementById('property_amenities');
            var container = document.getElementById('amenity-tags-container');
            
            if (amenitiesField && container) {
                var currentAmenities = amenitiesField.value ? amenitiesField.value.split(',') : [];
                if (!currentAmenities.includes(amenity)) {
                    currentAmenities.push(amenity);
                    amenitiesField.value = currentAmenities.join(',');
                    addAmenityTag(amenity);
                }
            }
        }
        
        function addFeatureTag(feature) {
            var container = document.getElementById('feature-tags-container');
            if (container) {
                var tag = document.createElement('span');
                tag.className = 'resbs-feature-tag';
                var escapedFeature = feature.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                tag.innerHTML = escapedFeature + ' <button type="button" class="remove-tag" data-feature="' + escapedFeature + '">×</button>';
                container.appendChild(tag);
                
                // Add remove functionality
                tag.querySelector('.remove-tag').addEventListener('click', function() {
                    removeFeature(feature);
                    tag.remove();
                });
            }
        }
        
        function addAmenityTag(amenity) {
            var container = document.getElementById('amenity-tags-container');
            if (container) {
                var tag = document.createElement('span');
                tag.className = 'resbs-feature-tag';
                var escapedAmenity = amenity.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
                tag.innerHTML = escapedAmenity + ' <button type="button" class="remove-tag" data-amenity="' + escapedAmenity + '">×</button>';
                container.appendChild(tag);
                
                // Add remove functionality
                tag.querySelector('.remove-tag').addEventListener('click', function() {
                    removeAmenity(amenity);
                    tag.remove();
                });
            }
        }
        
        function removeFeature(feature) {
            var featuresField = document.getElementById('property_features');
            if (featuresField) {
                var currentFeatures = featuresField.value ? featuresField.value.split(',') : [];
                var index = currentFeatures.indexOf(feature);
                if (index > -1) {
                    currentFeatures.splice(index, 1);
                    featuresField.value = currentFeatures.join(',');
                }
            }
        }
        
        function removeAmenity(amenity) {
            var amenitiesField = document.getElementById('property_amenities');
            if (amenitiesField) {
                var currentAmenities = amenitiesField.value ? amenitiesField.value.split(',') : [];
                var index = currentAmenities.indexOf(amenity);
                if (index > -1) {
                    currentAmenities.splice(index, 1);
                    amenitiesField.value = currentAmenities.join(',');
                }
            }
        }
    });
    
})(jQuery);

