// Single place to switch the backend target.
// Local dev (same WiFi as your Laravel server): use your machine's LAN IP.
// Production: swap to your live domain, then rebuild the APK.
const API_BASE_URL = 'http://192.168.1.8:8000';

// Restrict this key to your Android app's package name + SHA-1 fingerprint
// in Google Cloud Console — the web-restricted key already used by the
// Blade version will NOT work inside the app (no HTTP referrer in a WebView).
const GOOGLE_MAPS_API_KEY = '';
