@extends('driver.layout')

@section('title', 'Forgot Password')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8 bg-gradient-to-br from-green-50 to-white">
    <div class="w-full max-w-md">
        <!-- Back Button -->
        <a href="{{ route('driver.login') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800 mb-6">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Login
        </a>

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-green-600 rounded-full mb-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Reset Password</h1>
            <p class="text-gray-600 mt-2">Enter your phone number to reset password</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-2xl shadow-xl p-6" x-data="forgotPasswordForm()">
            <form @submit.prevent="submitForm">
                <!-- Phone Number Input -->
                <div class="mb-6">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <input
                            type="tel"
                            id="phone"
                            x-model="phone"
                            placeholder="Enter registered phone number"
                            class="w-full pl-12 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-600 focus:ring-2 focus:ring-green-200 transition-all"
                            required
                            pattern="[0-9]{10}"
                            maxlength="10"
                        >
                    </div>
                </div>

                <!-- Error/Success Message -->
                <div x-show="errorMessage" x-transition class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600" x-text="errorMessage"></p>
                </div>

                <div x-show="successMessage" x-transition class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-600" x-text="successMessage"></p>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                >
                    <span x-show="!loading">Send Reset Link</span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Sending...
                    </span>
                </button>
            </form>

            <!-- Info -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-800">
                    💡 A password reset OTP will be sent to your registered phone number
                </p>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function forgotPasswordForm() {
        return {
            phone: '',
            loading: false,
            errorMessage: '',
            successMessage: '',

            async submitForm() {
                this.errorMessage = '';
                this.successMessage = '';

                if (this.phone.length !== 10) {
                    this.errorMessage = 'Please enter a valid 10-digit phone number';
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch('/api/driver/forgot-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            phone: this.phone
                        })
                    });

                    const data = await response.json();

                    if (response.ok) {
                        this.successMessage = 'Password reset OTP sent successfully!';

                        // Store phone and redirect after 2 seconds
                        sessionStorage.setItem('driver_phone', this.phone);
                        setTimeout(() => {
                            window.location.href = '{{ route("driver.otp") }}';
                        }, 2000);
                    } else {
                        this.errorMessage = data.message || 'Failed to send reset link. Please try again.';
                    }
                } catch (error) {
                    this.errorMessage = 'Network error. Please check your connection.';
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endsection
