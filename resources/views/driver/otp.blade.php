@extends('driver.layout')

@section('title', 'Verify OTP')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8 bg-gradient-to-br from-green-50 to-white">
    <div class="w-full max-w-md">
        <!-- Back Button -->
        <a href="{{ route('driver.login') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800 mb-6">
            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back
        </a>

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-green-600 rounded-full mb-4">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Verify OTP</h1>
            <p class="text-gray-600 mt-2">Enter the 6-digit code sent to</p>
            <p class="text-green-600 font-semibold" x-data x-text="sessionStorage.getItem('driver_phone') || 'your phone'"></p>
            <p
                class="text-sm text-amber-600 font-medium mt-2"
                x-data
                x-show="sessionStorage.getItem('driver_otp_debug')"
                x-text="'Debug OTP: ' + sessionStorage.getItem('driver_otp_debug')"
            ></p>
        </div>

        <!-- OTP Form -->
        <div class="bg-white rounded-2xl shadow-xl p-6" x-data="otpForm()">
            <form @submit.prevent="submitOTP()">
                <!-- OTP Input Boxes -->
                <div class="flex justify-center gap-2 mb-6">
                    <template x-for="i in 6" :key="i">
                        <input
                            type="text"
                            maxlength="1"
                            pattern="[0-9]"
                            :id="'otp-' + i"
                            x-model="otp[i-1]"
                            @input="handleInput($event, i)"
                            @keydown="handleKeydown($event, i)"
                            class="w-12 h-14 text-center text-xl font-bold border-2 border-gray-200 rounded-xl focus:border-green-600 focus:ring-2 focus:ring-green-200 transition-all"
                            required
                        >
                    </template>
                </div>

                <!-- Error Message -->
                <div x-show="errorMessage" x-transition class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-600" x-text="errorMessage"></p>
                </div>

                <!-- Resend OTP -->
                <div class="text-center mb-6">
                    <p class="text-sm text-gray-600">
                        Didn't receive code?
                        <button
                            type="button"
                            @click="resendOTP()"
                            :disabled="resendTimer > 0 || loading"
                            class="text-green-600 hover:text-green-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span x-show="resendTimer > 0">Resend in <span x-text="resendTimer"></span>s</span>
                            <span x-show="resendTimer === 0">Resend OTP</span>
                        </button>
                    </p>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="loading || otp.join('').length !== 6"
                    class="w-full bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-semibold py-3 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                >
                    <span x-show="!loading">Verify & Login</span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Verifying...
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function otpForm() {
        return {
            otp: ['', '', '', '', '', ''],
            loading: false,
            errorMessage: '',
            resendTimer: 30,
            timerInterval: null,

            init() {
                // Focus first input
                document.getElementById('otp-1')?.focus();

                // Start resend timer
                this.startResendTimer();
            },

            handleInput(event, index) {
                const value = event.target.value;

                // Only allow numbers
                if (!/^\d*$/.test(value)) {
                    event.target.value = '';
                    return;
                }

                // Move to next input
                if (value && index < 6) {
                    document.getElementById('otp-' + (index + 1))?.focus();
                }
            },

            handleKeydown(event, index) {
                // Handle backspace
                if (event.key === 'Backspace' && !this.otp[index - 1] && index > 1) {
                    document.getElementById('otp-' + (index - 1))?.focus();
                }
            },

            startResendTimer() {
                this.resendTimer = 30;

                this.timerInterval = setInterval(() => {
                    if (this.resendTimer > 0) {
                        this.resendTimer--;
                    } else {
                        clearInterval(this.timerInterval);
                    }
                }, 1000);
            },

            async resendOTP() {
                const phone = sessionStorage.getItem('driver_phone');

                if (!phone) {
                    window.location.href = '{{ route("driver.login") }}';
                    return;
                }

                this.loading = true;
                this.errorMessage = '';

                try {
                    const response = await fetch('/api/driver/send-otp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ phone })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        sessionStorage.removeItem('driver_otp_debug');

                        if (data.otp) {
                            sessionStorage.setItem('driver_otp_debug', data.otp);
                        }

                        showToast('OTP sent successfully', 'success');
                        this.startResendTimer();
                    } else {
                        this.errorMessage = data.message || 'Failed to resend OTP';
                    }
                } catch (error) {
                    this.errorMessage = 'Network error. Please try again.';
                } finally {
                    this.loading = false;
                }
            },

            async submitOTP() {
                const otpCode = this.otp.join('');
                const phone = sessionStorage.getItem('driver_phone');

                if (otpCode.length !== 6) {
                    this.errorMessage = 'Please enter complete 6-digit OTP';
                    return;
                }

                if (!phone) {
                    window.location.href = '{{ route("driver.login") }}';
                    return;
                }

                this.loading = true;
                this.errorMessage = '';

                try {
                    const response = await fetch('/api/driver/verify-otp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            phone: phone,
                            otp: otpCode
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Store auth token
                        localStorage.setItem('driver_token', data.token);
                        localStorage.setItem('driver_data', JSON.stringify(data.driver));
                        sessionStorage.removeItem('driver_otp_debug');

                        // Redirect to dashboard
                        window.location.href = '{{ route("driver.dashboard") }}';
                    } else {
                        this.errorMessage = data.message || 'Invalid OTP. Please try again.';
                        this.otp = ['', '', '', '', '', ''];
                        document.getElementById('otp-1')?.focus();
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
