# OpenStreetMap Setup Guide

## ✅ No Setup Required!

Your real estate site is now using **FREE OpenStreetMap** with Leaflet.js. This means:

- **No API keys needed**
- **No billing required**
- **No account setup**
- **Unlimited usage**
- **Works everywhere**

## How It Works

1. **Maps**: Uses OpenStreetMap tiles (completely free)
2. **Geocoding**: Uses Nominatim service (free OpenStreetMap geocoding)
3. **Library**: Leaflet.js (open-source JavaScript mapping library)

## Features Included

✅ Interactive maps with zoom and pan  
✅ Property markers with custom colors  
✅ Clickable markers with property info popups  
✅ Automatic geocoding for properties without coordinates  
✅ Map bounds automatically adjust to show all properties  
✅ All properties displayed on map  

## Settings Location

**WordPress Admin → RealEstate Booking Suite → Settings → Map Tab**

The settings page will show a green success message confirming OpenStreetMap is active. No configuration needed!

## Property Coordinates

Each property needs latitude and longitude coordinates:

1. Edit any property
2. Go to **Location** tab
3. Enter **Latitude** and **Longitude** in the "Map Coordinates" section
4. Click **Update**

If coordinates are missing, the system will automatically geocode the address using the free Nominatim service.

## Rate Limits

Nominatim (geocoding service) has a usage policy:
- **1 request per second** (already handled in code)
- For high-volume sites, consider caching geocoded results

## Troubleshooting

**Map not showing?**
- Check browser console (F12) for errors
- Ensure internet connection is active
- Verify map container is visible

**Properties not appearing?**
- Check that properties have coordinates set
- Verify coordinates are valid (lat: -90 to 90, lng: -180 to 180)
- Check browser console for geocoding status

## Technical Details

- **Leaflet Version**: 1.9.4
- **OpenStreetMap Tiles**: Standard OSM tiles
- **Geocoding API**: Nominatim (nominatim.openstreetmap.org)
- **Rate Limiting**: Built-in 1 second delay between geocoding requests

