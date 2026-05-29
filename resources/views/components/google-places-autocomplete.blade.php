@props([
    'id' => 'google-places-autocomplete',
    'name' => 'google_places_search',
    'placeholder' => 'Search for an address...',
    'class' => '',
])

<div x-data="googlePlacesAutocomplete('{{ $id }}')" class="relative">
    <input 
        type="text" 
        id="{{ $id }}"
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent {{ $class }}"
        x-ref="input"
        autocomplete="off"
    />
    
    <div x-show="loading" class="absolute right-3 top-1/2 transform -translate-y-1/2">
        <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>
</div>

<script>
function googlePlacesAutocomplete(inputId) {
    return {
        loading: false,
        autocomplete: null,
        selectedPlace: null,
        
        init() {
            this.initAutocomplete();
        },
        
        initAutocomplete() {
            if (typeof google === 'undefined') {
                console.error('Google Maps JavaScript API is not loaded');
                return;
            }
            
            this.autocomplete = new google.maps.places.Autocomplete(this.$refs.input, {
                types: ['address'],
                componentRestrictions: { country: 'IN' }, // Restrict to India, change as needed
                fields: [
                    'place_id',
                    'formatted_address',
                    'name',
                    'geometry',
                    'address_components',
                    'types'
                ]
            });
            
            this.autocomplete.addListener('place_changed', () => {
                this.loading = true;
                const place = this.autocomplete.getPlace();
                
                if (!place.geometry) {
                    console.log('No details available for input: ' + place.name);
                    this.loading = false;
                    return;
                }
                
                this.selectedPlace = place;
                this.fillAddressFields(place);
                this.loading = false;
                
                // Dispatch custom event with place data
                this.$dispatch('place-selected', {
                    place: place,
                    placeId: place.place_id,
                    formattedAddress: place.formatted_address,
                    lat: place.geometry.location.lat(),
                    lng: place.geometry.location.lng(),
                    addressComponents: place.address_components
                });
            });
        },
        
        fillAddressFields(place) {
            const addressComponents = place.address_components;
            const componentMap = {};
            
            // Map Google Places address components to our form fields
            addressComponents.forEach(component => {
                const types = component.types;
                if (types.includes('street_number')) {
                    componentMap.street_number = component.long_name;
                } else if (types.includes('route')) {
                    componentMap.route = component.long_name;
                } else if (types.includes('locality')) {
                    componentMap.city = component.long_name;
                } else if (types.includes('administrative_area_level_1')) {
                    componentMap.state = component.long_name;
                } else if (types.includes('postal_code')) {
                    componentMap.postal_code = component.long_name;
                } else if (types.includes('country')) {
                    componentMap.country = component.long_name;
                }
            });
            
            // Fill form fields if they exist
            this.setFieldValue('address_line1', 
                (componentMap.street_number || '') + ' ' + (componentMap.route || '')
            );
            this.setFieldValue('city', componentMap.city || '');
            this.setFieldValue('state', componentMap.state || '');
            this.setFieldValue('zip_code', componentMap.postal_code || '');
            this.setFieldValue('country', componentMap.country || '');
            
            // Set coordinates and Google Places data
            this.setFieldValue('lat', place.geometry.location.lat());
            this.setFieldValue('lng', place.geometry.location.lng());
            this.setFieldValue('google_places_data', JSON.stringify(place));
        },
        
        setFieldValue(fieldName, value) {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.value = value;
                // Trigger change event for form validation
                field.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    };
}
</script>

@push('scripts')
<script>
    // Load Google Maps JavaScript API if not already loaded
    if (typeof google === 'undefined') {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps.api_key') }}&libraries=places&callback=initMap`;
        script.async = true;
        document.head.appendChild(script);
        
        window.initMap = function() {
            console.log('Google Maps API loaded successfully');
        };
    }
</script>
@endpush