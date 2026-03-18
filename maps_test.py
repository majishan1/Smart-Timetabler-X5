import requests

# --- 1. SETTINGS ---
API_KEY = "eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6ImI3OWU3MzE2NDY0MjRkOThhYjY1ZmQ1NmFkYzU4NGUxIiwiaCI6Im11cm11cjY0In0="

# OpenRouteService transport modes: 
# 'foot-walking', 'driving-car', 'cycling-regular', 'wheelchair'
TRANSPORT_MODE = "foot-walking" 

HOME_POSTCODE = "M13 9PL, Manchester, UK"
EVENT_LOCATION = "Piccadilly Gardens, Manchester, UK"


# --- 2. THE GEOCODING FUNCTION (Text -> Coordinates) ---
def get_coordinates(location_name):
    print(f"🌍 Searching for coordinates for: {location_name}...")
    url = f"https://api.openrouteservice.org/geocode/search?api_key={API_KEY}&text={location_name}"
    
    response = requests.get(url)
    
    if response.status_code == 200:
        data = response.json()
        if len(data['features']) > 0:
            # ORS returns coordinates as [Longitude, Latitude]
            coords = data['features'][0]['geometry']['coordinates']
            print(f"   ✅ Found! Longitude: {coords[0]}, Latitude: {coords[1]}")
            return coords
        else:
            print("   ❌ Could not find this location.")
            return None
    else:
        print(f"   🛑 API Error {response.status_code}: {response.text}")
        return None


# --- 3. THE ROUTING FUNCTION (Coordinates -> Time) ---
def get_travel_time(start_coords, end_coords, mode):
    print(f"\n🚶‍♂️ Calculating {mode} time...")
    
    # ORS requires coordinates in the format "lon,lat"
    start_str = f"{start_coords[0]},{start_coords[1]}"
    end_str = f"{end_coords[0]},{end_coords[1]}"
    
    url = f"https://api.openrouteservice.org/v2/directions/{mode}?api_key={API_KEY}&start={start_str}&end={end_str}"
    
    response = requests.get(url)
    
    if response.status_code == 200:
        data = response.json()
        
        # The API returns the duration in seconds
        duration_seconds = data['features'][0]['properties']['summary']['duration']
        
        # Convert seconds to minutes and round it up
        duration_minutes = round(duration_seconds / 60)
        
        print(f"   ✅ Success! It will take {duration_minutes} minutes.")
        return duration_minutes
    else:
        print(f"   🛑 API Error {response.status_code}: {response.text}")
        return None


# --- 4. RUN THE TEST ---
print("--- STARTING API TEST ---")

home_coords = get_coordinates(HOME_POSTCODE)
event_coords = get_coordinates(EVENT_LOCATION)

if home_coords and event_coords:
    commute_time = get_travel_time(home_coords, event_coords, TRANSPORT_MODE)
    
    if commute_time is not None:
        print("\n🎉 TEST COMPLETE! API IS WORKING PERFECTLY.")
        print(f"To get to your event on time, you need to leave {commute_time} minutes early!")
else:
    print("\n🛑 Test failed. Check your API key or location names.")