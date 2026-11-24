@props(['record'=>null,'readonly'=>false])
@php($consented = $readonly || old('location_consent', $record?->location_consent_at ? '1':null))
<div class="location-panel" data-location-panel data-readonly="{{ $readonly ? '1':'0' }}" data-google-key="{{ config('services.google_maps.key') }}" data-weather-url="{{ URL::temporarySignedRoute('weather.show',now()->addHour()) }}">
@if(!$readonly)<label class="check-label"><input type="checkbox" name="location_consent" value="1" data-location-consent @checked($consented)> <span>Save these coordinates with this record. I have permission to share this location with Google Maps and OpenWeather when loading the map and weather.</span></label>@endif
<div class="form-grid location-inputs"><div><label for="latitude">Latitude</label><input id="latitude" data-latitude name="latitude" type="number" min="-90" max="90" step="0.0000001" value="{{ old('latitude',$record?->latitude ?? '14.5995') }}" @readonly($readonly) @disabled(!$consented)></div><div><label for="longitude">Longitude</label><input id="longitude" data-longitude name="longitude" type="number" min="-180" max="180" step="0.0000001" value="{{ old('longitude',$record?->longitude ?? '120.9842') }}" @readonly($readonly) @disabled(!$consented)></div></div>
<p class="muted small-copy">Default preview: Manila (14.5995, 120.9842). {{ $readonly ? 'Load the saved location to see its map and current weather.' : 'Enter coordinates manually or click the configured map to select a location. Leaving the box unchecked saves no new coordinates.' }}</p>
<button class="btn secondary small" type="button" data-load-location @disabled(!$consented)>Load Map & Weather</button>
<div class="location-map" data-map role="region" aria-label="Location map"><p>Map preview will appear here when loaded. You can enter coordinates even before a Maps key is configured.</p></div>
<p data-map-status class="muted small-copy" role="status"></p>
<div class="weather-panel" role="status" aria-live="polite"><span class="eyebrow">CURRENT WEATHER</span><p data-weather-status>Weather has not been loaded.</p></div>
</div>
@once @push('scripts')<script src="{{ asset('js/location.js') }}?v={{ filemtime(public_path('js/location.js')) }}" defer></script>@endpush @endonce
