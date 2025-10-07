/**
 * Professional Admin Dashboard JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        RESBS_Admin_Dashboard.init();
    });

    // Admin Dashboard object
    window.RESBS_Admin_Dashboard = {
        
        /**
         * Initialize dashboard functionality
         */
        init: function() {
            this.initCharts();
            this.initRealTimeUpdates();
            this.initQuickActions();
            this.initTooltips();
        },

        /**
         * Initialize charts
         */
        initCharts: function() {
            var $chartCanvas = $('#resbs-property-chart');
            
            if ($chartCanvas.length && typeof Chart !== 'undefined') {
                this.createPropertyChart($chartCanvas[0]);
            } else if ($chartCanvas.length) {
                // Load Chart.js if not already loaded
                this.loadChartJS().then(function() {
                    RESBS_Admin_Dashboard.createPropertyChart($chartCanvas[0]);
                });
            }
        },

        /**
         * Load Chart.js library
         */
        loadChartJS: function() {
            return new Promise(function(resolve, reject) {
                if (typeof Chart !== 'undefined') {
                    resolve();
                    return;
                }
                
                var script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        },

        /**
         * Create property statistics chart
         */
        createPropertyChart: function(canvas) {
            var ctx = canvas.getContext('2d');
            
            // Get data from server
            $.ajax({
                url: resbs_dashboard.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_get_property_chart_data',
                    nonce: resbs_dashboard.nonce
                },
                success: function(response) {
                    if (response.success) {
                        RESBS_Admin_Dashboard.renderChart(ctx, response.data);
                    }
                },
                error: function() {
                    RESBS_Admin_Dashboard.renderDefaultChart(ctx);
                }
            });
        },

        /**
         * Render chart with data
         */
        renderChart: function(ctx, data) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: resbs_dashboard.strings.properties_added || 'Properties Added',
                        data: data.values,
                        borderColor: '#0073aa',
                        backgroundColor: 'rgba(0, 115, 170, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#0073aa',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#0073aa',
                            borderWidth: 1,
                            cornerRadius: 6,
                            displayColors: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#666',
                                font: {
                                    size: 12
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                color: '#666',
                                font: {
                                    size: 12
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        },

        /**
         * Render default chart when no data available
         */
        renderDefaultChart: function(ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Properties Added',
                        data: [0, 0, 0, 0, 0, 0],
                        borderColor: '#0073aa',
                        backgroundColor: 'rgba(0, 115, 170, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });
        },

        /**
         * Initialize real-time updates
         */
        initRealTimeUpdates: function() {
            // Update stats every 30 seconds
            setInterval(function() {
                RESBS_Admin_Dashboard.updateStats();
            }, 30000);
        },

        /**
         * Update statistics
         */
        updateStats: function() {
            $.ajax({
                url: resbs_dashboard.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_get_dashboard_stats',
                    nonce: resbs_dashboard.nonce
                },
                success: function(response) {
                    if (response.success) {
                        RESBS_Admin_Dashboard.animateStats(response.data);
                    }
                }
            });
        },

        /**
         * Animate statistics updates
         */
        animateStats: function(data) {
            $('.resbs-stat-number').each(function() {
                var $this = $(this);
                var currentValue = parseInt($this.text()) || 0;
                var newValue = data[$this.closest('.resbs-stat-item').index()] || currentValue;
                
                if (newValue !== currentValue) {
                    RESBS_Admin_Dashboard.animateNumber($this, currentValue, newValue);
                }
            });
        },

        /**
         * Animate number change
         */
        animateNumber: function($element, start, end) {
            var duration = 1000;
            var startTime = null;
            
            function animate(currentTime) {
                if (startTime === null) startTime = currentTime;
                var progress = Math.min((currentTime - startTime) / duration, 1);
                
                var current = Math.floor(start + (end - start) * progress);
                $element.text(current);
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                }
            }
            
            requestAnimationFrame(animate);
        },

        /**
         * Initialize quick actions
         */
        initQuickActions: function() {
            $('.resbs-action-item').on('click', function(e) {
                var $this = $(this);
                var href = $this.attr('href');
                
                // Add loading state
                $this.addClass('loading');
                
                // Remove loading state after navigation
                setTimeout(function() {
                    $this.removeClass('loading');
                }, 1000);
            });
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            // Add tooltips to stat items
            $('.resbs-stat-item').each(function() {
                var $this = $(this);
                var label = $this.find('.resbs-stat-label').text();
                $this.attr('title', label);
            });
            
            // Add tooltips to action items
            $('.resbs-action-item').each(function() {
                var $this = $(this);
                var label = $this.find('span:last-child').text();
                $this.attr('title', label);
            });
        },

        /**
         * Show notification
         */
        showNotification: function(message, type) {
            type = type || 'info';
            
            var $notification = $('<div class="resbs-notification resbs-notification-' + type + '">' +
                '<span class="resbs-notification-message">' + message + '</span>' +
                '<button type="button" class="resbs-notification-close">×</button>' +
                '</div>');
            
            $('body').append($notification);
            
            // Show notification
            setTimeout(function() {
                $notification.addClass('show');
            }, 100);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 5000);
            
            // Close button
            $notification.find('.resbs-notification-close').on('click', function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            });
        },

        /**
         * Format number with commas
         */
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },

        /**
         * Format currency
         */
        formatCurrency: function(amount, currency) {
            currency = currency || 'USD';
            
            var symbols = {
                'USD': '$',
                'EUR': '€',
                'GBP': '£',
                'JPY': '¥'
            };
            
            var symbol = symbols[currency] || '$';
            return symbol + this.formatNumber(amount);
        },

        /**
         * Get relative time
         */
        getRelativeTime: function(date) {
            var now = new Date();
            var diff = now - new Date(date);
            var seconds = Math.floor(diff / 1000);
            var minutes = Math.floor(seconds / 60);
            var hours = Math.floor(minutes / 60);
            var days = Math.floor(hours / 24);
            
            if (days > 0) {
                return days + ' day' + (days > 1 ? 's' : '') + ' ago';
            } else if (hours > 0) {
                return hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
            } else if (minutes > 0) {
                return minutes + ' minute' + (minutes > 1 ? 's' : '') + ' ago';
            } else {
                return 'Just now';
            }
        }
    };

    // Add notification styles
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .resbs-notification {
                position: fixed;
                top: 32px;
                right: 20px;
                background: #fff;
                border-left: 4px solid #0073aa;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                padding: 15px 20px;
                border-radius: 4px;
                z-index: 999999;
                transform: translateX(100%);
                transition: transform 0.3s ease;
                max-width: 300px;
            }
            
            .resbs-notification.show {
                transform: translateX(0);
            }
            
            .resbs-notification-success {
                border-left-color: #28a745;
            }
            
            .resbs-notification-error {
                border-left-color: #dc3545;
            }
            
            .resbs-notification-warning {
                border-left-color: #ffc107;
            }
            
            .resbs-notification-message {
                display: block;
                margin-right: 20px;
                font-size: 14px;
                color: #333;
            }
            
            .resbs-notification-close {
                position: absolute;
                top: 10px;
                right: 10px;
                background: none;
                border: none;
                font-size: 18px;
                color: #666;
                cursor: pointer;
                padding: 0;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .resbs-notification-close:hover {
                color: #333;
            }
            
            .resbs-action-item.loading {
                opacity: 0.6;
                pointer-events: none;
            }
            
            .resbs-action-item.loading::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 20px;
                height: 20px;
                margin: -10px 0 0 -10px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #0073aa;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
        `)
        .appendTo('head');

})(jQuery);
