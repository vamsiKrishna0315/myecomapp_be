@extends('driver.layout')

@section('title', 'Profile')

@section('content')
<div class="min-h-screen bg-gray-50" x-data="driverProfile()" x-init="init()">

    <!-- Header -->
    <header class="bg-gradient-to-br from-green-600 to-green-700 text-white">
        <div class="px-4 py-6">
            <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center text-white mb-4">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Dashboard
            </a>

            <div class="flex items-center">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center text-green-600 font-bold text-2xl mr-4">
                    <span x-text="getInitials()"></span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold" x-text="driver.name"></h1>
                    <p class="text-green-100" x-text="driver.phone"></p>
                </div>
            </div>
        </div>
    </header>

    <!-- Profile Content -->
    <div class="px-4 py-6 space-y-4">

        <!-- Personal Information Card -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-500">PERSONAL INFORMATION</h2>
            </div>
            <div class="p-4 space-y-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500">Full Name</p>
                        <p class="font-medium text-gray-800" x-text="driver.name"></p>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500">Phone Number</p>
                        <p class="font-medium text-gray-800" x-text="driver.phone"></p>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500">Email</p>
                        <p class="font-medium text-gray-800" x-text="driver.email || 'Not provided'"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Information Card -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="p-4 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-500">VEHICLE INFORMATION</h2>
            </div>
            <div class="p-4 space-y-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500">Vehicle Type</p>
                        <p class="font-medium text-gray-800" x-text="driver.vehicle_type || 'Bike'"></p>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500">Vehicle Number</p>
                        <p class="font-medium text-gray-800" x-text="driver.vehicle_number || 'Not provided'"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="bg-white rounded-xl shadow-sm p-4">
            <h2 class="text-sm font-semibold text-gray-500 mb-4">STATISTICS</h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="text-center p-4 bg-green-50 rounded-xl">
                    <p class="text-2xl font-bold text-green-600" x-text="driver.total_deliveries || 0"></p>
                    <p class="text-xs text-gray-600 mt-1">Total Deliveries</p>
                </div>
                <div class="text-center p-4 bg-blue-50 rounded-xl">
                    <p class="text-2xl font-bold text-blue-600" x-text="driver.rating || '4.8'"></p>
                    <p class="text-xs text-gray-600 mt-1">Rating</p>
                </div>
            </div>
        </div>

        <!-- Settings / Actions -->
        <div class="bg-white rounded-xl shadow-sm">
            <button class="w-full p-4 flex items-center justify-between border-b border-gray-100">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="font-medium text-gray-800">Notifications</span>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <button class="w-full p-4 flex items-center justify-between border-b border-gray-100">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium text-gray-800">Delivery History</span>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <button class="w-full p-4 flex items-center justify-between border-b border-gray-100">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="font-medium text-gray-800">Help & Support</span>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>

            <button class="w-full p-4 flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="font-medium text-gray-800">Settings</span>
                </div>
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>

        <!-- Logout Button -->
        <button
            @click="logout"
            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-4 px-6 rounded-xl shadow-lg transition-all flex items-center justify-center"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            Logout
        </button>

        <!-- App Version -->
        <div class="text-center py-4">
            <p class="text-xs text-gray-500">Driver App v1.0.0</p>
            <p class="text-xs text-gray-400 mt-1">© 2025 All rights reserved</p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function driverProfile() {
        return {
            driver: {
                name: 'Loading...',
                phone: '',
                email: '',
                vehicle_type: '',
                vehicle_number: '',
                total_deliveries: 0,
                rating: 0
            },

            init() {
                this.checkAuth();
                this.loadProfile();
            },

            checkAuth() {
                const token = localStorage.getItem('driver_token');
                if (!token) {
                    driverLogout();
                }
            },

            async loadProfile() {
                const token = localStorage.getItem('driver_token');

                // Try to load from API
                try {
                    const response = await fetch('/api/driver/profile', {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                        }
                    });

                    const data = await handleDriverApiResponse(response, 'Failed to load profile.');

                    if (!data) {
                        return;
                    }

                    if (!data.error) {
                        this.driver = data;
                        localStorage.setItem('driver_data', JSON.stringify(this.driver));
                    } else {
                        this.loadFromStorage();
                    }
                } catch (error) {
                    this.loadFromStorage();
                }
            },

            loadFromStorage() {
                const driverData = JSON.parse(localStorage.getItem('driver_data') || '{}');
                this.driver = {
                    name: driverData.name || 'John Driver',
                    phone: driverData.phone || '+91 98765 43210',
                    email: driverData.email || 'driver@example.com',
                    vehicle_type: driverData.vehicle_type || 'Bike',
                    vehicle_number: driverData.vehicle_number || 'DL 01 AB 1234',
                    total_deliveries: driverData.total_deliveries || 127,
                    rating: driverData.rating || '4.8'
                };
            },

            getInitials() {
                if (!this.driver.name) return 'D';
                const names = this.driver.name.split(' ');
                if (names.length >= 2) {
                    return (names[0][0] + names[1][0]).toUpperCase();
                }
                return this.driver.name.substring(0, 2).toUpperCase();
            },

            logout() {
                if (confirm('Are you sure you want to logout?')) {
                    driverLogout();
                }
            }
        }
    }
</script>
@endsection
