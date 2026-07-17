# Driver App — Capacitor Native Implementation Plan

> **Living document.** Update the status column/checkboxes as tasks complete.
> Any agent/session picking this up should read this file first before touching
> driver-app code, to know what's done, what's in progress, and what's next.

## Goal

Wrap the existing Laravel-served driver PWA (`resources/views/driver/*.blade.php`)
into a native Android app via **Capacitor**, so it gets:
1. Background lat/lng tracking (works with phone locked/app backgrounded)
2. Native push notifications for new order assignments
3. Native camera capture for proof-of-delivery images

Decision context: TWA/Bubblewrap was considered and rejected — it requires a
public HTTPS URL even for local testing (blocked by "no ngrok, no production
yet" constraint) and cannot do reliable background geolocation or native push
(hard browser limitations, not config issues). React Native was considered
and rejected — it requires a full frontend rewrite; Capacitor reuses the
existing Blade/Alpine/Tailwind UI as-is.

**No redesign.** Every screen should look and behave the same as the current
Blade version. This is a repackaging + native-capability project, not a UI
project.

## Current state (as of Phase 1 backend work)

Already done, do not redo:
- Driver auth (OTP), orders (list/detail), order status updates, order
  cancellation, driver profile get/update, location ping endpoint — all real,
  tested endpoints under `/api/driver/*` (see `routes/driver-api-sample.php`).
- Backend services: `DriverOrderService`, `DriverOrderStatusService`,
  `DriverOrderCancellationService`, `DriverTripTrackingService`,
  `DriverOtpService`.
- Blade views: `resources/views/driver/{layout,login,otp,forgot-password,
  dashboard,order-detail,order-map,profile}.blade.php`.
- See `DRIVER_APP_PHASE1_IMPLEMENTATION.md` for what was fixed there
  (including two unrelated production bugs in `OrdersObserver` and
  `WhatsAppServiceInterface` found and fixed).

None of the above needs to change for this plan except: swapping relative
`fetch('/api/driver/...')` calls for an `API_BASE_URL`-prefixed absolute URL
(Phase A below).

---

## Phases

### Phase A — Convert Blade views to a static Capacitor bundle
**Status: Done (2026-07-16)**

Snapshot each Blade view's rendered output into static HTML/CSS/JS under
`capacitor-app/www/`. Same Tailwind classes, same Alpine.js logic — only
change is how it's served and how it calls the API.

Tasks:
- [x] Decide/create bundle directory structure (`capacitor-app/www/`)
- [x] Create single JS config file with `API_BASE_URL` constant
      (`capacitor-app/www/js/config.js`)
- [x] Convert `login.blade.php` → static `login.html`
- [x] Convert `otp.blade.php` → static `otp.html`
- [x] Convert `forgot-password.blade.php` → static `forgot-password.html`
- [x] Convert `dashboard.blade.php` → static `dashboard.html`
- [x] Convert `order-detail.blade.php` → static `order-detail.html`
- [x] Convert `order-map.blade.php` → static `order-map.html`
- [x] Convert `profile.blade.php` → static `profile.html`
- [x] Added `index.html` entry point (redirects to dashboard/login based on
      stored token — mirrors the old `/driver` route behavior)
- [x] Extracted shared layout logic (`layout.blade.php`'s inline script) into
      `capacitor-app/www/js/common.js`, minus PWA-only bits (service worker
      registration, `beforeinstallprompt`) which don't apply to a native shell
- [x] Replaced every relative `fetch('/api/driver/...')` with
      `` fetch(`${API_BASE_URL}/api/driver/...`) ``
- [x] Resolved Blade-only server values: `route('driver.*')` helpers →
      static `*.html` filenames; order IDs passed via `?id=` query string
      instead of server-side route params; `config('services.google.maps.api_key')`
      → `GOOGLE_MAPS_API_KEY` constant in `config.js`, loaded lazily via
      `loadGoogleMapsScript()` in `common.js` only on pages that need it
- [ ] Verify: open the static bundle in a browser directly, confirm all
      screens render and API calls succeed against local Laravel server
      (blocked on Phase B — needs `API_BASE_URL` pointed at a reachable
      Laravel instance to test against)

**Estimate: 1–1.5 days — actual: same session**

**Follow-ups / things to revisit, not blockers:**
- `GOOGLE_MAPS_API_KEY` in `config.js` is currently empty — the existing
  web-restricted Google Maps key won't work inside a WebView (no HTTP
  referrer). Needs a new key restricted to the Android package name + SHA-1
  fingerprint (generated in Phase B/F once the keystore exists).
- Tailwind and Alpine.js are still loaded from CDN (`cdn.tailwindcss.com`,
  `cdn.jsdelivr.net`) exactly as the Blade version did — matches "no
  redesign," but means first launch needs internet to fetch them. Worth
  vendoring these as local files in a later pass for the background-tracking
  use case (driver may have poor connectivity).
- `capacitor-app/www/js/config.js`'s `API_BASE_URL` is set to
  `http://192.168.1.8:8000` (user's WiFi adapter IPv4, confirmed via
  `ipconfig` — "Wireless LAN adapter Wi-Fi 2"). Needs `php artisan serve
  --host=0.0.0.0 --port=8000` running for this to resolve (Herd's `.test`
  domains don't resolve from the phone).

---

### Phase B — Capacitor project setup
**Status: Done (2026-07-16)**

Tasks:
- [x] `npm init` + install `@capacitor/core`, `@capacitor/cli` (in
      `capacitor-app/`, separate `package.json` from the Laravel app)
- [x] `npx cap init` — app name `Yumeat Driver`, package id
      `com.yumeat.driverapp`, web dir `www`
- [x] `npx cap add android`
- [x] Point Capacitor config `webDir` at the Phase A bundle (`www`, set at
      init time)
- [x] Enable `usesCleartextTraffic` — added directly to
      `capacitor-app/android/app/src/main/AndroidManifest.xml`'s
      `<application>` tag (the `server.cleartext: true` capacitor.config.json
      option didn't get auto-injected on `cap add android`/`cap sync`, so set
      explicitly). **Must be removed before a production release build.**
- [x] `npx cap sync` + build a first debug APK — `BUILD SUCCESSFUL`,
      `capacitor-app/android/app/build/outputs/apk/debug/app-debug.apk`
- [x] Installed on user's real device (Redmi/POCO, MIUI — required sideload
      via `adb push` to Downloads + manual install, since MIUI blocks
      `adb install` without a Mi-account-gated "Install via USB" toggle)
- [x] Fixed two real blockers found during device testing (not anticipated
      in the original plan):
      1. **Mixed Content block** — Capacitor serves local pages over a
         virtual `https://localhost` origin by default; calling the
         `http://` LAN API from that https page was blocked outright, before
         cleartext/CORS even mattered. Fixed via
         `"androidScheme": "http"` in `capacitor.config.json`, so the app
         serves over `http://localhost` (same scheme as the API call).
      2. **CORS** — `config/cors.php`'s `allowed_origins` didn't include
         `http://localhost`/`https://localhost` (the Capacitor app's fixed
         origin regardless of target API). Added both, plus a Pest test in
         `tests/Feature/Cors/CustomerOtpCorsTest.php` mirroring the existing
         origin-allowlist pattern.
- [x] Confirmed working end-to-end on device: login → OTP → dashboard →
      order detail. **Not yet checked: the live map view** (order-map.html /
      Google Maps rendering) — deferred, `GOOGLE_MAPS_API_KEY` in
      `config.js` is still empty (needs an Android-restricted key — see
      Phase A follow-ups).
- [x] Minor UI fix along the way: added `space-y-4` to the pending/active/
      completed tab containers in `dashboard.blade.php` and
      `capacitor-app/www/dashboard.html` — order cards had no gap between
      them.

**Depends on:** Phase A
**Estimate: 0.5–1 day — actual: same session**

**Environment setup done this session (reusable for future phases):**
- `ANDROID_HOME` / `ANDROID_SDK_ROOT` → `D:\Development\AndrodSdk`,
  `JAVA_HOME` → `D:\Development\AndrodStudio\jbr` — set persistently via
  `setx` (visible in new terminal sessions/reboots) — note: tool-invoked
  shells in this session didn't inherit `setx` changes made in a different
  invocation, so also set `sdk.dir` directly in
  `capacitor-app/android/local.properties` as a more reliable fallback.
- Confirmed working: Android Studio (bundled JBR/JDK 21) + Android SDK
  (build-tools 35/36, platform android-36, platform-tools/adb) at
  `D:\Development\AndrodStudio` / `D:\Development\AndrodSdk` — both already
  installed by the user, no fresh install needed.
- Node v22.13.1 / npm 11.1.0 confirmed.

---

### Phase C — Background geolocation
**Status: Implemented, pending device verification (2026-07-16)**

Tasks:
- [x] Added `@capacitor-community/background-geolocation` (uses a foreground
      service + persistent notification approach, not the heavier
      `ACCESS_BACKGROUND_LOCATION` permission — avoids Google Play's
      background-location review process entirely)
- [x] Added `@capacitor/local-notifications` (needed to request
      `POST_NOTIFICATIONS` on Android 13+ for the tracking notification)
- [x] Config additions in `capacitor.config.json`:
      - `android.useLegacyBridge: true` — prevents the plugin's location
        updates halting after 5 min backgrounded (documented plugin
        requirement)
      - `plugins.CapacitorHttp.enabled: true` — routes `fetch()` through
        native networking instead of the WebView's, since Android throttles
        WebView-originated HTTP requests after ~5 min backgrounded (would
        have silently broken location POSTs otherwise)
- [x] Confirmed via merged manifest
      (`android/app/build/intermediates/merged_manifest/debug/...`) that all
      needed permissions landed automatically: `ACCESS_FINE_LOCATION`,
      `ACCESS_COARSE_LOCATION`, `FOREGROUND_SERVICE`,
      `FOREGROUND_SERVICE_LOCATION`, `POST_NOTIFICATIONS`, `WAKE_LOCK`
- [x] Rewrote `capacitor-app/www/js/common.js` location tracking:
      `startDriverLocationTracking()` now detects native platform via
      `window.Capacitor.isNativePlatform()` and uses
      `BackgroundGeolocation.addWatcher()` (distance-filtered, 30m) instead
      of the old `setInterval`-based foreground-only polling. Falls back to
      the old browser-geolocation polling if the plugin isn't present (e.g.
      previewing the bundle in a plain browser). Added
      `stopDriverLocationTracking()`, called on logout.
- [x] Still posts to the existing `POST /api/driver/location` endpoint — no
      backend change needed (`DriverLocationController`,
      `DriverTripTrackingService` untouched)
- [x] Debug APK built successfully with both new plugins, pushed to device
- [ ] **Device-test still needed**: confirm the permission prompts appear
      correctly (location, then the background/"allow all the time"-style
      flow this plugin uses, then notification permission), and that
      location updates keep arriving with the app backgrounded / screen
      locked. Watch for the persistent "Yumeat Driver is tracking your
      location" notification — if it's not showing, tracking will stop
      after ~5 min per Android's background service rules.

**Depends on:** Phase B
**Estimate: 1–1.5 days — actual: implementation same session, device
verification pending**

---

### Phase D — Push notifications (new order alerts)
**Status: Not started — blocked on external setup**

**Blocking dependency:** user needs a Firebase project with Cloud Messaging
enabled + `google-services.json`. This is a console/account step only the
user can do — flag and wait if not yet available.

**2026-07-16: User explicitly asked to hold Phase D and do Phase E first.**
No Firebase project created yet. Resume here when ready — instructions for
creating the project and registering the Android app (package id
`com.yumeat.driverapp`) were already given to the user in conversation.

Backend tasks (new work, not yet built):
- [ ] Migration: add device token storage (`drivers.fcm_token` column, or a
      `driver_devices` table if multi-device support wanted — decide before
      building)
- [ ] New endpoint: `POST /api/driver/device-token` to register/update token
      on login
- [ ] Hook into order-assignment flow (see `AssignDriverToOrderJobTest.php`
      for where a driver gets assigned) to dispatch an FCM push when an order
      is assigned
- [ ] Pest tests for device-token registration + assignment-triggers-push

Frontend tasks:
- [ ] Add `@capacitor/push-notifications`
- [ ] Request notification permission, retrieve device token
- [ ] Send token to new `/api/driver/device-token` endpoint on login
- [ ] Handle notification tap → deep-link into order-detail screen

**Depends on:** Phase B, Firebase project from user
**Estimate: 1.5–2 days** (Firebase setup + delivery-reliability testing is
usually the slow part, not the code)

---

### Phase E — Native camera (proof-of-delivery upload)
**Status: Implemented, pending device verification (2026-07-16)**

Backend tasks:
- [x] Decided: **separate `proof_of_deliveries` table**, not a column on
      `orders` (per user's explicit direction — supports multiple photos per
      order later, mirrors the existing `order_status_tracking` table
      convention). Columns: `order_id`, `driver_id`, `order_status_id`,
      `status_code` (denormalized snapshot, same pattern as
      `OrderStatusTracking`), `image_path`, `status` (active flag).
      Migration: `2026_07_16_111118_create_proof_of_deliveries_table.php`.
      Model: `app/Models/ProofOfDelivery.php`.
- [x] Storage: uses the existing `App\Services\Media\MediaService`
      abstraction (not raw `Storage::` calls) — added
      `MediaCategory::ProofOfDelivery` (private category) in
      `app/Enums/MediaCategory.php`. Since `.env` has
      `MEDIA_PROVIDER=supabase` already active, uploads automatically go to
      **Supabase** (per user's explicit direction to match how all other
      images are stored) — no extra wiring needed, `MediaService::upload()`
      already routes there.
- [x] New endpoint: `POST /api/driver/orders/{orderId}/proof-of-delivery` —
      `UploadProofOfDeliveryRequest` (validates `image`, max 5MB,
      jpeg/png/jpg) → `DriverProofOfDeliveryController` →
      `DriverProofOfDeliveryService` (finds the driver's order, uploads via
      `MediaService`, creates the `ProofOfDelivery` row, returns a public
      URL).
- [x] Pest tests in `tests/Feature/DriverProofOfDeliveryTest.php` — upload
      success, non-image rejection (422), wrong-driver rejection (404). All
      passing. `Http::fake()` used to intercept the real Supabase HTTP call,
      matching the existing `MediaServiceTest` convention.

Frontend tasks:
- [x] Added `@capacitor/camera`. Confirmed via merged manifest inspection
      that no `CAMERA` permission is needed — this plugin version launches
      the system camera app via an intent (`ACTION_IMAGE_CAPTURE`) rather
      than accessing hardware directly.
- [x] Added shared `capturePod(orderId)` helper in
      `capacitor-app/www/js/common.js` — takes a photo via
      `Capacitor.Plugins.Camera.getPhoto()`, converts the returned
      `webPath` to a `Blob`, uploads via `FormData` to the new endpoint.
      Returns `false` (silently) if the driver cancels the camera, or
      `false` with a toast if the upload fails — callers must check this
      before proceeding.
- [x] Wired into **both** places a driver can mark an order delivered:
      `order-detail.html`'s `updateStatus()` and `dashboard.html`'s
      `updateOrderStatus()` — when the target status is `'delivered'`,
      capture + upload happens first; the status-update call is skipped
      entirely if capture/upload fails or is cancelled.
- [x] Debug APK built successfully with the camera plugin.
- [ ] **Device-test still needed** — phone disconnected from USB before the
      updated APK could be pushed. Once reconnected: push
      `capacitor-app/android/app/build/outputs/apk/debug/app-debug.apk`,
      reinstall, and verify marking an order "Delivered" opens the camera,
      uploads successfully, and only then updates the order status.

**Depends on:** Phase B
**Estimate: 1–1.5 days — actual: implementation same session, device
verification pending**

---

### Phase F — Build, sign, test end-to-end
**Status: Not started**

Tasks:
- [ ] Generate release keystore (store location TBD — sensitive, ask user)
- [ ] Build signed release APK
- [ ] Full manual pass on a real device:
  - [ ] Login → OTP → dashboard loads orders
  - [ ] Accept order → status updates progress correctly
  - [ ] Location keeps updating with app backgrounded/locked
  - [ ] Push notification received for a newly-assigned order, tap opens
        correct order
  - [ ] Delivery photo capture + upload succeeds
  - [ ] Cancel-before-acceptance flow still works
- [ ] Fix native-wrapper-specific issues that don't show up in browser
      testing

**Depends on:** Phases A–E
**Estimate: 0.5–1 day**

---

## Total estimate: 6–8.5 working days

## Suggested build order
A → B (get an installable native app ASAP) → C (stated top priority) → D → E
→ F. Each phase after B adds one capability without blocking the others, so
partial progress is always demoable.

## Open decisions needing user input before/during specific phases
- Package ID / app name for `npx cap init` (Phase B)
- Multi-device support for push tokens: single `fcm_token` column vs a
  `driver_devices` table (Phase D)
- Proof-of-delivery image storage: new `orders` column vs dedicated table
  (Phase E)
- Release keystore storage location (Phase F)
- Firebase project availability (blocks Phase D start)

## Session log
- 2026-07-16: Plan created. Phase 1 backend (profile + cancellation
  endpoints) already complete, documented separately in
  `DRIVER_APP_PHASE1_IMPLEMENTATION.md`. No Capacitor work started yet.
- 2026-07-16: Phase A complete. All 7 driver screens + entry point converted
  to static HTML under `capacitor-app/www/`, shared JS extracted to
  `js/common.js` + `js/config.js`. Nothing deleted from `resources/views/driver/`
  — the Blade/PWA version still exists untouched alongside this.
- 2026-07-16: Phase B — Capacitor project scaffolded (`capacitor-app/`,
  app id `com.yumeat.driverapp`), Android platform added, first debug APK
  built successfully. `API_BASE_URL` set to user's confirmed LAN IP
  `http://192.168.1.8:8000`. User needs to run
  `php artisan serve --host=0.0.0.0 --port=8000` to make that reachable, and
  connect their phone via USB with debugging enabled to install/test the APK
  — not done yet this session. Next: install APK on device, verify
  login → OTP → dashboard → order detail end to end, then Phase C
  (background geolocation).
