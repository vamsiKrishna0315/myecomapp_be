// Shared across every page, ported from resources/views/driver/layout.blade.php.
// PWA-only bits (service worker registration, beforeinstallprompt) were dropped —
// this now runs as a native-wrapped app, not a browser PWA.

let driverLocationTracker = null;
let driverLocationTrackerStarted = false;

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-600' : type === 'error' ? 'bg-red-600' : 'bg-blue-600';

    toast.className = `${bgColor} text-white px-4 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full`;
    toast.textContent = message;

    document.getElementById('toast-container').appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);

    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

let loadingOverlay = null;

function showLoading() {
    if (!loadingOverlay) {
        loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        loadingOverlay.innerHTML = `
            <div class="bg-white rounded-lg p-6">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
    }
}

function hideLoading() {
    if (loadingOverlay) {
        loadingOverlay.remove();
        loadingOverlay = null;
    }
}

function driverLogout(redirectUrl = 'login.html') {
    stopDriverLocationTracking();
    localStorage.removeItem('driver_token');
    localStorage.removeItem('driver_data');
    sessionStorage.removeItem('driver_phone');
    sessionStorage.removeItem('driver_otp_debug');
    window.location.href = redirectUrl;
}

async function handleDriverApiResponse(response, fallbackMessage = 'Request failed.') {
    if (response.status === 401 || response.status === 403) {
        driverLogout();
        return null;
    }

    if (response.ok) {
        return response.json();
    }

    const data = await response.json().catch(() => ({}));

    return {
        error: true,
        message: data.message || fallbackMessage,
        data,
    };
}

function shouldTrackDriverLocation() {
    const page = window.location.pathname.split('/').pop();

    return !['', 'login.html', 'otp.html', 'forgot-password.html'].includes(page);
}

async function postDriverLocation(lat, lng, accuracyMeters) {
    const token = localStorage.getItem('driver_token');

    if (!token) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE_URL}/api/driver/location`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                lat,
                lng,
                accuracy_meters: accuracyMeters,
            }),
        });

        await handleDriverApiResponse(response, 'Failed to update driver location.');
    } catch (error) {
        console.error('Driver location update failed:', error);
    }
}

// Foreground-only fallback, used when the native background geolocation
// plugin isn't available (e.g. previewing this bundle in a plain browser).
async function sendDriverLocationUpdateOnce() {
    if (!navigator.geolocation) {
        return;
    }

    return new Promise((resolve) => {
        navigator.geolocation.getCurrentPosition((position) => {
            void postDriverLocation(
                position.coords.latitude,
                position.coords.longitude,
                position.coords.accuracy,
            ).finally(resolve);
        }, () => resolve(), {
            enableHighAccuracy: true,
            maximumAge: 60000,
            timeout: 15000,
        });
    });
}

function startDriverLocationTracking() {
    if (driverLocationTrackerStarted || !shouldTrackDriverLocation()) {
        return;
    }

    driverLocationTrackerStarted = true;

    const plugins = window.Capacitor?.Plugins;

    if (window.Capacitor?.isNativePlatform?.() && plugins?.BackgroundGeolocation) {
        startNativeBackgroundTracking(plugins);
        return;
    }

    // Browser fallback: foreground-only polling.
    void sendDriverLocationUpdateOnce();
    driverLocationTracker = window.setInterval(() => {
        void sendDriverLocationUpdateOnce();
    }, 120000);
}

async function startNativeBackgroundTracking(plugins) {
    if (plugins.LocalNotifications) {
        try {
            await plugins.LocalNotifications.requestPermissions();
        } catch (error) {
            console.error('Notification permission request failed:', error);
        }
    }

    try {
        const watcherId = await plugins.BackgroundGeolocation.addWatcher(
            {
                backgroundTitle: 'Yumeat Driver is tracking your location',
                backgroundMessage: 'Cancel to stop location tracking for this delivery.',
                requestPermissions: true,
                stale: false,
                distanceFilter: 30,
            },
            (location, error) => {
                if (error) {
                    console.error('Background location error:', error);
                    return;
                }

                if (location) {
                    void postDriverLocation(location.latitude, location.longitude, location.accuracy);
                }
            },
        );

        driverLocationTracker = watcherId;
    } catch (error) {
        console.error('Failed to start background location tracking:', error);
    }
}

function stopDriverLocationTracking() {
    if (!driverLocationTrackerStarted) {
        return;
    }

    driverLocationTrackerStarted = false;

    const plugins = window.Capacitor?.Plugins;

    if (window.Capacitor?.isNativePlatform?.() && plugins?.BackgroundGeolocation && driverLocationTracker) {
        plugins.BackgroundGeolocation.removeWatcher({ id: driverLocationTracker }).catch(() => {});
    } else if (driverLocationTracker) {
        window.clearInterval(driverLocationTracker);
    }

    driverLocationTracker = null;
}

// Captures a photo with the native camera and uploads it as proof of
// delivery for the given order. Returns true on success, false if the driver
// cancelled the capture or the upload failed (a toast is already shown).
async function capturePod(orderId) {
    const plugins = window.Capacitor?.Plugins;

    if (!window.Capacitor?.isNativePlatform?.() || !plugins?.Camera) {
        showToast('Camera is not available on this device.', 'error');
        return false;
    }

    let photo;
    try {
        photo = await plugins.Camera.getPhoto({
            quality: 70,
            resultType: 'uri',
            source: 'CAMERA',
        });
    } catch (error) {
        // Driver cancelled the camera — not an error, just abort silently.
        return false;
    }

    const token = localStorage.getItem('driver_token');
    showLoading();

    try {
        const fileResponse = await fetch(photo.webPath);
        const blob = await fileResponse.blob();

        const formData = new FormData();
        formData.append('image', blob, 'proof-of-delivery.jpg');

        const response = await fetch(`${API_BASE_URL}/api/driver/orders/${orderId}/proof-of-delivery`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
            },
            body: formData,
        });

        const data = await handleDriverApiResponse(response, 'Failed to upload proof of delivery.');

        if (!data || data.error) {
            showToast(data?.message || 'Failed to upload proof of delivery.', 'error');
            return false;
        }

        showToast('Proof of delivery captured', 'success');
        return true;
    } catch (error) {
        console.error('Proof of delivery upload failed:', error);
        showToast('Failed to upload proof of delivery.', 'error');
        return false;
    } finally {
        hideLoading();
    }
}

function loadGoogleMapsScript(callback) {
    if (!GOOGLE_MAPS_API_KEY) {
        return;
    }

    if (window.google && window.google.maps) {
        callback();
        return;
    }

    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${GOOGLE_MAPS_API_KEY}`;
    script.async = true;
    script.defer = true;
    if (callback) {
        script.onload = callback;
    }
    document.head.appendChild(script);
}

window.addEventListener('load', () => {
    startDriverLocationTracking();
});
