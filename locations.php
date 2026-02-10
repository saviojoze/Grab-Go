<?php
$page_title = 'Our Locations - Grab & Go';
$current_page = 'locations';
require_once 'config.php';
require_once 'includes/header.php';
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>

<!-- Leaflet Routing Machine -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<style>
    #map { 
        height: 500px; 
        width: 100%; 
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .location-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }
    .location-info {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 16px;
    }
    .status-box {
        padding: 10px;
        border-radius: 6px;
        background: #f8f9fa;
        font-size: 0.9rem;
        margin-top: 10px;
        border-left: 4px solid #05CD99;
    }
    /* Hide routing instructions container to keep UI clean */
    .leaflet-routing-container {
        display: none !important;
    }
</style>

<div class="container" style="padding-top: 30px; padding-bottom: 50px;">
    <div class="row" style="display: flex; flex-wrap: wrap; gap: 30px;">
        
        <!-- Info Column -->
        <div style="flex: 1; min-width: 300px;">
            <h1 style="font-size: 2rem; margin-bottom: 10px;">Find Us</h1>
            <p style="color: #666; margin-bottom: 30px;">Visit our supermarket or see the route from your location!</p>
            
            <div class="location-card">
                <h3 style="margin-bottom: 15px; color: #05CD99;">Grab & Go Supermarket</h3>
                <div class="location-info">
                    <div style="display: flex; gap: 10px; align-items: flex-start;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #05CD99; margin-top: 2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span>
                            <strong>Main Branch</strong><br>
                            Town Center,<br>
                            Kanjirappally, 686507
                        </span>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #05CD99;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <span>+91 98765 43210</span>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #05CD99;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span>Open Daily: 8:00 AM - 10:00 PM</span>
                    </div>
                </div>
            </div>

            <div class="location-card">
                <h3>Your Status</h3>
                <div id="status-msg" class="status-box">Requesting your location...</div>
                <button onclick="locateUser()" class="btn btn-primary" style="margin-top: 15px; width: 100%;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                    Re-center on Me
                </button>
            </div>
        </div>

        <!-- Map Column -->
        <div style="flex: 2; min-width: 300px;">
            <div id="map"></div>
        </div>
    </div>
</div>

<script>
    // 1. Initialize Map
    // Coordinates for Kanjirappally
    const supermarketLoc = [9.557270, 76.789436]; 
    const map = L.map('map').setView(supermarketLoc, 13);

    // 2. Add Tiles (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // 3. Routing Control Variable
    let routingControl = null;

    // 4. Locate User Function
    const statusMsg = document.getElementById('status-msg');
    
    function locateUser() {
        statusMsg.innerText = "Locating you...";
        statusMsg.style.borderLeftColor = "#05CD99";
        
        if (!navigator.geolocation) {
            statusMsg.innerText = "❌ Geolocation is not supported by your browser.";
            statusMsg.style.borderLeftColor = "#EE5D50";
            return;
        }

        const options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        };

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const userLat = position.coords.latitude;
                const userLng = position.coords.longitude;
                const userLoc = [userLat, userLng];

                // Update Status
                statusMsg.innerText = "✅ Location Found! Routing...";
                statusMsg.style.borderLeftColor = "#05CD99"; // Green
                
                // Remove existing routing control if any
                if (routingControl) {
                    map.removeControl(routingControl);
                }

                // Create Routing Control
                routingControl = L.Routing.control({
                    waypoints: [
                        L.latLng(userLat, userLng), // Start (User)
                        L.latLng(supermarketLoc[0], supermarketLoc[1]) // End (Supermarket)
                    ],
                    routeWhileDragging: false,
                    showAlternatives: false,
                    lineOptions: {
                        styles: [{color: '#05CD99', opacity: 0.8, weight: 6}]
                    },
                    createMarker: function(i, wp, nWps) {
                        if (i === 0) {
                            // User Marker
                             return L.marker(wp.latLng, {
                                icon: L.icon({
                                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                                    iconSize: [25, 41],
                                    iconAnchor: [12, 41],
                                    popupAnchor: [1, -34],
                                    shadowSize: [41, 41]
                                }),
                                draggable: false
                            }).bindPopup("<b>You are here</b>");
                        } else {
                            // Store Marker (End)
                            return L.marker(wp.latLng, {
                                icon: L.icon({
                                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                                    iconSize: [25, 41],
                                    iconAnchor: [12, 41],
                                    popupAnchor: [1, -34],
                                    shadowSize: [41, 41]
                                }),
                                draggable: false
                            }).bindPopup("<b>Grab & Go</b><br>We are here!");
                        }
                    }
                }).addTo(map);
                
                statusMsg.innerText = "✅ Route Calculated!";

            },
            (error) => {
                let errorMsg = "❌ Unable to retrieve location.";
                statusMsg.style.borderLeftColor = "#EE5D50"; // Red
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = "❌ Access denied.<br><br>1. Click the 🔒 Lock/Info icon in your URL bar.<br>2. Set 'Location' to 'Allow'.<br>3. Refresh the page.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = "❌ Location unavailable. Check your device settings.";
                        break;
                    case error.TIMEOUT:
                        errorMsg = "❌ Request timed out. Please try again.";
                        break;
                    case error.UNKNOWN_ERROR:
                        errorMsg = "❌ An unknown error occurred.";
                        break;
                }
                statusMsg.innerText = errorMsg;
                console.error("Geolocation Error:", error);
            },
            options
        );
    }

    // Auto-locate on load
    locateUser();
</script>

<?php require_once 'includes/footer.php'; ?>
