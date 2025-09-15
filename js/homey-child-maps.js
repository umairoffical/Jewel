function initializeCircleMap(map, latitude, longitude) {
    // Create circle radius
    var circleOptions = {
        strokeColor: '#FF0000',
        strokeOpacity: 0.2,
        strokeWeight: 2,
        fillColor: '#FF0000',
        fillOpacity: 0.1,
        map: map,
        center: { lat: parseFloat(latitude), lng: parseFloat(longitude) },
        radius: 12874.2  // 8 miles in meters (1 mile = 1609.27 meters)
    };

    // Add the circle to the map
    var circle = new google.maps.Circle(circleOptions);

    // Adjust map bounds to fit circle
    var bounds = circle.getBounds();
    map.fitBounds(bounds);

    // Remove existing markers if any
    if (window.mainMarker) {
        window.mainMarker.setMap(null);
    }
}

// Override the original homey map initialization
if (typeof homey_map_init === 'function') {
    var original_homey_map_init = homey_map_init;
    homey_map_init = function(map_margin) {
        original_homey_map_init(map_margin);
        
        // Get the map instance
        var map = window.googleMap;
        
        if (map && typeof google !== 'undefined') {
            // Get listing coordinates from the page
            var latitude = jQuery('#latitude').val();
            var longitude = jQuery('#longitude').val();
            
            if (latitude && longitude) {
                initializeCircleMap(map, latitude, longitude);
            }
        }
    };
}

jQuery(document).ready(function($) {
    var homeyMap;
    var propertyMarker;
    
    if($('#homey-single-map-child').length > 0) {
        var mapDiv = $('#homey-single-map-child');
        var zoomlevel = mapDiv.data('zoom');
        var _lat = mapDiv.data('lat');
        var _long = mapDiv.data('long');
        var element = 'homey-single-map-child';
        var defaultZoom = zoomlevel || 13;
        
        homeySimpleMap(_lat, _long, element, false, true, defaultZoom);
    }

    function homeySimpleMap(_lat, _long, element, markerDragable, showCircle, defaultZoom) {
        if (!markerDragable) {
            markerDragable = false;
        }

        if (!showCircle) {
            showCircle = false;
        }

        if (!defaultZoom) {
            defaultZoom = 15;
        }

        var mapCenter = L.latLng(_lat, _long);
        
        var mapOptions = {
            dragging: markerDragable,
            center: mapCenter,
            zoom: defaultZoom,
            tap: false
        };

        var mapElement = document.getElementById(element);
        homeyMap = L.map(mapElement, mapOptions);

        homeyMap.scrollWheelZoom.disable();
        
        // Add the tile layer (map background)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(homeyMap);

        // Create circle with 8 mile radius (in meters)
        var circle = L.circle(mapCenter, {
            color: '#FF0000',
            fillColor: '#FF0000',
            fillOpacity: 0.1,
            radius: 9656.06 // 6 miles in meters
        }).addTo(homeyMap);

        // Fit map bounds to circle
        homeyMap.fitBounds(circle.getBounds());

        // If not showing circle, add marker
        if (!showCircle) {
            propertyMarker = L.marker(mapCenter, {
                draggable: markerDragable,
                riseOnHover: true
            }).addTo(homeyMap);
        }

        // Handle map click events if marker is draggable
        if (markerDragable) {
            homeyMap.on('click', function(e) {
                var marker = e.latlng;
                if (propertyMarker) {
                    propertyMarker.setLatLng(marker);
                }
                
                $('#latitude').val(marker.lat);
                $('#longitude').val(marker.lng);
            });

            propertyMarker.on('dragend', function(e) {
                var marker = e.target.getLatLng();
                $('#latitude').val(marker.lat);
                $('#longitude').val(marker.lng);
            });
        }
    }
}); 