/**
 * Geolocation for Tel Code Autofill
 * Extracted from inline script in single-property.php
 * 
 * @package RealEstate_Booking_Suite
 */

(function() {
    'use strict';
    
    // Country code to tel code mapping
    const countryToTelCode = {
        'US': '+1', 'CA': '+1',
        'GB': '+44', 'FR': '+33', 'DE': '+49', 'JP': '+81', 'CN': '+86',
        'IN': '+91', 'AU': '+61', 'RU': '+7', 'IT': '+39', 'ES': '+34',
        'NL': '+31', 'BE': '+32', 'CH': '+41', 'SE': '+46', 'NO': '+47',
        'DK': '+45', 'FI': '+358', 'PT': '+351', 'GR': '+30', 'PL': '+48',
        'HU': '+36', 'CZ': '+420', 'AE': '+971', 'SA': '+966', 'ZA': '+27',
        'BR': '+55', 'MX': '+52', 'AR': '+54', 'CL': '+56', 'NZ': '+64',
        'SG': '+65', 'MY': '+60', 'TH': '+66', 'VN': '+84', 'ID': '+62',
        'KR': '+82', 'HK': '+852', 'TW': '+886', 'BD': '+880', 'NP': '+977',
        'PK': '+92', 'LK': '+94', 'MM': '+95', 'KH': '+855', 'LA': '+856',
        'BN': '+673', 'TL': '+670', 'AF': '+93', 'IR': '+98', 'IQ': '+964',
        'LB': '+961', 'JO': '+962', 'IL': '+972', 'PS': '+970', 'EG': '+20',
        'MA': '+212', 'DZ': '+213', 'TN': '+216', 'LY': '+218', 'SD': '+249',
        'KE': '+254', 'NG': '+234', 'GH': '+233', 'UG': '+256', 'TZ': '+255',
        'TR': '+90', 'PH': '+63', 'VE': '+58', 'CO': '+57', 'PE': '+51',
        'CU': '+53', 'AT': '+43', 'RO': '+40', 'UA': '+380', 'RS': '+381',
        'ME': '+382', 'XK': '+383', 'HR': '+385', 'SI': '+386', 'BA': '+387',
        'MK': '+389', 'SK': '+421', 'LI': '+423', 'LT': '+370', 'LV': '+371',
        'EE': '+372', 'MD': '+373', 'AM': '+374', 'BY': '+375', 'AD': '+376',
        'MC': '+377', 'SM': '+378', 'GI': '+350', 'LU': '+352', 'IE': '+353',
        'IS': '+354', 'AL': '+355', 'MT': '+356', 'CY': '+357', 'BG': '+359',
        'TJ': '+992', 'TM': '+993', 'AZ': '+994', 'GE': '+995', 'KG': '+996',
        'UZ': '+998', 'MV': '+960', 'SY': '+963', 'KW': '+965', 'YE': '+967',
        'OM': '+968', 'BH': '+973', 'QA': '+974', 'BT': '+975', 'MN': '+976',
        'KP': '+850', 'MO': '+853'
    };
    
    function setTelCodeByCountry(countryCode) {
        const telCode = countryToTelCode[countryCode];
        const phoneCodeSelect = document.getElementById('bookingPhoneCode');
        
        if (telCode && phoneCodeSelect) {
            for (let i = 0; i < phoneCodeSelect.options.length; i++) {
                if (phoneCodeSelect.options[i].value === telCode) {
                    phoneCodeSelect.selectedIndex = i;
                    break;
                }
            }
        }
    }
    
    // Method 1: Try browser geolocation API
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${lat}&longitude=${lng}&localityLanguage=en`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.countryCode) {
                            setTelCodeByCountry(data.countryCode);
                        }
                    })
                    .catch(error => {
                        detectCountryByIP();
                    });
            },
            function(error) {
                detectCountryByIP();
            },
            { timeout: 5000, enableHighAccuracy: false }
        );
    } else {
        detectCountryByIP();
    }
    
    // Method 2: IP-based geolocation (fallback)
    function detectCountryByIP() {
        fetch('https://ipapi.co/json/')
            .then(response => response.json())
            .then(data => {
                if (data.country_code) {
                    setTelCodeByCountry(data.country_code);
                }
            })
            .catch(error => {
                fetch('https://api.country.is/')
                    .then(response => response.json())
                    .then(data => {
                        if (data.country) {
                            setTelCodeByCountry(data.country);
                        }
                    })
                    .catch(function(err) {
                        // Alternative geolocation failed - silently continue
                    });
            });
    }
})();

