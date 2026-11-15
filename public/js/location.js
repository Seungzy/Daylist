(() => {
    const panel = document.querySelector('[data-location-panel]');
    if (!panel) return;
    const latInput = panel.querySelector('[data-latitude]');
    const lonInput = panel.querySelector('[data-longitude]');
    const consent = panel.querySelector('[data-location-consent]');
    const loadButton = panel.querySelector('[data-load-location]');
    const mapNode = panel.querySelector('[data-map]');
    const mapStatus = panel.querySelector('[data-map-status]');
    const weatherStatus = panel.querySelector('[data-weather-status]');
    const readonly = panel.dataset.readonly === '1';
    let map, marker, loading = false, weatherRequest, requestTimer;

    function coordinates() {
        const lat = Number(latInput.value), lng = Number(lonInput.value);
        if (!latInput.value || !lonInput.value || !Number.isFinite(lat) || !Number.isFinite(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) return null;
        return {lat,lng};
    }
    async function loadWeather() {
        const coords = coordinates();
        if (!coords || (!readonly && !consent.checked)) return;
        weatherRequest?.abort(); weatherRequest = new AbortController();
        weatherStatus.textContent = 'Loading current weather…';
        const url = new URL(panel.dataset.weatherUrl, window.location.href);
        url.searchParams.set('lat', String(coords.lat)); url.searchParams.set('lon', String(coords.lng));
        try {
            const response = await fetch(url, {headers:{Accept:'application/json'},signal:weatherRequest.signal});
            const result = await response.json();
            if (!response.ok) { weatherStatus.textContent = result.message || 'Weather is currently unavailable.'; return; }
            weatherStatus.textContent = `${result.place}: ${result.temperature}°C · ${result.description || 'Current conditions'} · Humidity ${result.humidity ?? '—'}%`;
        } catch (error) { if (error.name !== 'AbortError') weatherStatus.textContent = 'Weather is currently unavailable. Please try again later.'; }
    }
    function placeMarker() {
        const coords = coordinates();
        if (!coords || !map) return;
        if (!marker) marker = new google.maps.Marker({position:coords,map});
        else marker.setPosition(coords);
        map.panTo(coords);
    }
    window.initDaylistMap = () => {
        const coords = coordinates();
        if (!coords || (!readonly && !consent.checked)) { loading = false; return; }
        mapNode.replaceChildren();
        map = new google.maps.Map(mapNode,{center:coords,zoom:13,streetViewControl:false});
        if (!readonly) map.addListener('click', event => {
            latInput.value = event.latLng.lat().toFixed(7); lonInput.value = event.latLng.lng().toFixed(7);
            placeMarker(); clearTimeout(requestTimer); requestTimer = setTimeout(loadWeather,400);
        });
        placeMarker(); mapStatus.textContent = readonly ? 'Saved location.' : 'Click the map to select a location.';
        loading = false;
    };
    window.gm_authFailure = () => { mapStatus.textContent = 'Google Maps could not authenticate. Check the browser key, API, billing and allowed referrers.'; loading = false; };
    loadButton.addEventListener('click', () => {
        if (!readonly && !consent.checked) return;
        if (!coordinates()) { mapStatus.textContent = 'Enter valid latitude (-90 to 90) and longitude (-180 to 180).'; return; }
        loadWeather();
        if (map) { placeMarker(); return; }
        if (!panel.dataset.googleKey) { mapStatus.textContent = 'Interactive map is not configured yet. Your administrator can add GOOGLE_MAPS_KEY. Manual coordinates can still be saved.'; return; }
        if (window.google?.maps) { window.initDaylistMap(); return; }
        if (loading) return;
        loading = true; mapStatus.textContent = 'Loading map…';
        const script = document.createElement('script');
        const url = new URL('https://maps.googleapis.com/maps/api/js');
        url.searchParams.set('key',panel.dataset.googleKey); url.searchParams.set('loading','async'); url.searchParams.set('callback','initDaylistMap');
        script.src=url.toString(); script.async=true;
        script.onerror=()=>{ mapStatus.textContent='The map could not load. Check the internet connection and Maps settings.'; loading=false; script.remove(); };
        document.head.append(script);
    });
    [latInput,lonInput].forEach(input=>input.addEventListener('change',()=>{
        if (map) { placeMarker(); clearTimeout(requestTimer); requestTimer=setTimeout(loadWeather,400); }
    }));
    consent?.addEventListener('change',()=>{
        latInput.disabled=lonInput.disabled=loadButton.disabled=!consent.checked;
        if (!consent.checked) { weatherRequest?.abort(); clearTimeout(requestTimer); mapNode.replaceChildren(); mapNode.textContent='Location sharing is off. No new coordinates will be submitted.'; weatherStatus.textContent='Weather has not been loaded.'; mapStatus.textContent=''; map=null; marker=null; }
    });
})();
