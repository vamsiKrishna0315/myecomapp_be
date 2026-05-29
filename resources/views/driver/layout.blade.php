<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#16a34a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Driver App">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/icon-192.png">

    <title>@yield('title', 'Driver App')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom Styles -->
    <style>
        /* Disable pull-to-refresh */
        body {
            overscroll-behavior-y: contain;
            -webkit-tap-highlight-color: transparent;
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Custom green theme */
        :root {
            --primary-green: #16a34a;
            --primary-green-dark: #15803d;
        }

        /* Loading skeleton animation */
        @keyframes shimmer {
            0% { background-position: -468px 0; }
            100% { background-position: 468px 0; }
        }

        .skeleton {
            animation: shimmer 1.2s ease-in-out infinite;
            background: linear-gradient(to right, #f0f0f0 8%, #e0e0e0 18%, #f0f0f0 33%);
            background-size: 800px 104px;
        }

        /* Custom scrollbar for webkit */
        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-green);
            border-radius: 2px;
        }

        /* Card hover effect */
        .card-hover {
            transition: all 0.2s ease;
        }

        .card-hover:active {
            transform: scale(0.98);
        }

        /* Input focus ring green */
        input:focus, select:focus, textarea:focus {
            outline: none;
            ring-color: var(--primary-green);
            border-color: var(--primary-green);
        }
    </style>

    @yield('head')
</head>
<body class="bg-gray-50 font-sans antialiased">

    @yield('content')

    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <script>
        // PWA Install Prompt
        let deferredPrompt;
        let driverLocationTracker = null;
        let driverLocationTrackerStarted = false;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
        });

        // Global Toast Notification
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

        // Global Loading State
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

        function driverLogout(redirectUrl = '{{ route("driver.login") }}') {
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
            const path = window.location.pathname;

            if (!path.startsWith('/driver')) {
                return false;
            }

            return !['/driver/login', '/driver/otp', '/driver/forgot-password'].includes(path);
        }

        async function sendDriverLocationUpdate() {
            const token = localStorage.getItem('driver_token');

            if (!token || !navigator.geolocation) {
                return;
            }

            return new Promise((resolve) => {
                navigator.geolocation.getCurrentPosition(async (position) => {
                    try {
                        const response = await fetch('/api/driver/location', {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                lat: position.coords.latitude,
                                lng: position.coords.longitude,
                                accuracy_meters: position.coords.accuracy,
                            }),
                        });

                        await handleDriverApiResponse(response, 'Failed to update driver location.');
                    } catch (error) {
                        console.error('Driver location update failed:', error);
                    } finally {
                        resolve();
                    }
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
            void sendDriverLocationUpdate();

            driverLocationTracker = window.setInterval(() => {
                void sendDriverLocationUpdate();
            }, 120000);
        }

        // Register Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/service-worker.js')
                .then(reg => console.log('Service Worker registered'))
                .catch(err => console.log('Service Worker registration failed'));
        }

        window.addEventListener('load', () => {
            startDriverLocationTracking();
        });
    </script>

    @yield('scripts')
</body>
</html>
