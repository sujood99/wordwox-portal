<div>
    @if($loading && !$plan)
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading plan details...</p>
                </div>
            </div>
        </div>
    @elseif($error)
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="alert alert-danger" role="alert">
                        <h4 class="alert-heading">Error</h4>
                        <p>{{ $error }}</p>
                        <hr>
                        <a href="{{ route('home') }}" class="btn btn-primary">Return to Home</a>
                    </div>
                </div>
            </div>
        </div>
    @elseif($showFreeConfirmation && $plan)
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="row">
                        <!-- Order Summary - Left Side -->
                        <div class="col-lg-5 mb-4 mb-lg-0">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0 fw-bold"><i class="fas fa-shopping-cart me-2"></i>Order Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-2 fw-bold text-dark">{{ $plan->name }}</h5>
                                            @if($plan->description)
                                                <p class="text-dark small mb-0 fw-medium">{{ Str::limit($plan->description, 100) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="fw-semibold text-dark">Plan Type:</span>
                                        <span class="fw-bold text-dark">{{ $plan->type_label ?? 'Membership' }}</span>
                                    </div>
                                    
                                    @if($plan->duration_text)
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-semibold text-dark">Duration:</span>
                                            <span class="fw-bold text-dark">{{ $plan->duration_text }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($plan->totalQuota)
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-semibold text-dark">Total Sessions:</span>
                                            <span class="fw-bold text-dark">{{ $plan->totalQuota }}</span>
                                        </div>
                                    @endif
                                    
                                    <hr>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="fw-semibold text-dark">Subtotal:</span>
                                        <span class="fw-bold text-dark fs-5">{{ number_format($plan->price, 2) }} {{ $plan->currency ?? 'USD' }}</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                        <span class="h4 mb-0 fw-bold text-dark">Total:</span>
                                        <span class="h3 mb-0 fw-bold" style="color: #000000;">{{ number_format($plan->price, 2) }} {{ $plan->currency ?? 'USD' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Complete Order - Right Side -->
                        <div class="col-lg-7">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Complete Order</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <h6 class="fw-bold mb-3">Contact Information</h6>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">Email</label>
                                            <div class="form-control bg-light">
                                                {{ Auth::user()->orgUser->email ?? Auth::user()->email ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-muted small">Phone Number</label>
                                            <div class="row g-2 align-items-center">
                                                <div class="col-auto">
                                                    <select class="form-select" disabled style="height: 60px; width: 106px; background-color: var(--bs-secondary-bg);">
                                                        @php
                                                            $currentCountryCode = Auth::user()->orgUser->phoneCountry ?? 'JO';
                                                            $countries = $this->getSupportedCountries();
                                                            $currentCountry = null;
                                                            foreach ($countries as $iso => $country) {
                                                                if ($country['code'] == $currentCountryCode) {
                                                                    $currentCountry = $iso;
                                                                    break;
                                                                }
                                                            }
                                                        @endphp
                                                        @foreach($this->getSupportedCountries() as $iso => $country)
                                                            <option value="{{ $country['code'] }}" {{ ($currentCountry && $iso == $currentCountry) ? 'selected' : '' }}>
                                                                {{ $country['flag'] }} +{{ $country['code'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <div class="form-control bg-light" style="height: 56px; line-height: 38px; padding: 0.375rem 0.75rem; display: flex; align-items: center;">
                                                        {{ Auth::user()->orgUser->phoneNumber ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button wire:click="confirmFreeMembership" class="btn btn-primary btn-lg" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="confirmFreeMembership">
                                                <i class="fas fa-check me-2"></i>Complete order
                                            </span>
                                            <span wire:loading wire:target="confirmFreeMembership">
                                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                                Processing...
                                            </span>
                                        </button>
                                    </div>
                                    
                                    <div class="mt-4 pt-4 border-top text-center">
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-lock me-1"></i>
                                            Your order is secure
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($plan && !$paymentUrl && !$error && $plan->price > 0)
        {{-- Show plan details immediately, payment URL will load in background --}}
        <div class="container py-5" wire:poll.500ms>
            <div class="mb-3">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Packages
                </a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="row">
                        <!-- Order Summary - Left Side -->
                        <div class="col-lg-5 mb-4 mb-lg-0">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0 fw-bold"><i class="fas fa-shopping-cart me-2"></i>Order Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-2 fw-bold text-dark">{{ $plan->name }}</h5>
                                            @if($plan->description)
                                                <p class="text-dark small mb-0 fw-medium">{{ Str::limit($plan->description, 100) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="fw-semibold text-dark">Plan Type:</span>
                                        <span class="fw-bold text-dark">{{ $plan->type_label ?? 'Membership' }}</span>
                                    </div>
                                    
                                    @if($plan->duration_text)
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-semibold text-dark">Duration:</span>
                                            <span class="fw-bold text-dark">{{ $plan->duration_text }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($plan->totalQuota)
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-semibold text-dark">Total Sessions:</span>
                                            <span class="fw-bold text-dark">{{ $plan->totalQuota }}</span>
                                        </div>
                                    @endif
                                    
                                    <hr>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="fw-semibold text-dark">Subtotal:</span>
                                        <span class="fw-bold text-dark fs-5">{{ number_format($plan->price, 2) }} {{ $plan->currency ?? 'USD' }}</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                        <span class="h4 mb-0 fw-bold text-dark">Total:</span>
                                        <span class="h3 mb-0 fw-bold" style="color: #000000;">{{ number_format($plan->price, 2) }} {{ $plan->currency ?? 'USD' }}</span>
                                    </div>
                                    
                                    <div class="alert alert-info small mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Complete your payment using the secure payment form on the right.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Gateway - Right Side (Empty placeholder, will show iframe when ready) -->
                        <div class="col-lg-7">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Complete Payment</h5>
                                </div>
                                <div class="card-body p-0" style="min-height: 600px; position: relative; background: #f8f9fa;">
                                    {{-- Payment iframe will appear here when $paymentUrl is ready --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($paymentUrl && $plan)
        <div class="container py-5">
            <div class="mb-3">
                <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Packages
                </a>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="row">
                        <!-- Order Summary - Left Side -->
                        <div class="col-lg-5 mb-4 mb-lg-0">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0 fw-bold"><i class="fas fa-shopping-cart me-2"></i>Order Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-2 fw-bold text-dark">{{ $plan->name }}</h5>
                                            @if($plan->description)
                                                <p class="text-dark small mb-0 fw-medium">{{ Str::limit($plan->description, 100) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="fw-semibold text-dark">Plan Type:</span>
                                        <span class="fw-bold text-dark">{{ $plan->type_label ?? 'Membership' }}</span>
                                    </div>
                                    
                                    @if($plan->duration_text)
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-semibold text-dark">Duration:</span>
                                            <span class="fw-bold text-dark">{{ $plan->duration_text }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($plan->totalQuota)
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="fw-semibold text-dark">Total Sessions:</span>
                                            <span class="fw-bold text-dark">{{ $plan->totalQuota }}</span>
                                        </div>
                                    @endif
                                    
                                    <hr>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="fw-semibold text-dark">Subtotal:</span>
                                        <span class="fw-bold text-dark fs-5">{{ number_format($plan->price, 2) }} {{ $plan->currency ?? 'USD' }}</span>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                        <span class="h4 mb-0 fw-bold text-dark">Total:</span>
                                        <span class="h3 mb-0 fw-bold" style="color: #000000;">{{ number_format($plan->price, 2) }} {{ $plan->currency ?? 'USD' }}</span>
                                    </div>
                                    
                                    <div class="alert alert-info small mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Complete your payment using the secure payment form on the right.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Gateway - Right Side (Embedded) -->
                        <div class="col-lg-7">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Complete Payment</h5>
                                    <a href="{{ $paymentUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-1"></i>Open in New Tab
                                    </a>
                                </div>
                                <div class="card-body p-0" style="min-height: 600px; position: relative;">
                                    <iframe 
                                        src="{{ $paymentUrl }}" 
                                        frameborder="0" 
                                        class="w-100" 
                                        style="min-height: 600px; border: none; width: 100%;"
                                        id="payment-iframe"
                                        title="Payment Gateway"
                                        allow="payment"
                                        loading="lazy"
                                        sandbox="allow-forms allow-scripts allow-same-origin allow-top-navigation allow-popups allow-popups-to-escape-sandbox">
                                        <p class="p-3">Your browser does not support iframes. <a href="{{ $paymentUrl }}" target="_blank">Click here to open the payment page</a>.</p>
                                    </iframe>
                                </div>
                                <div class="card-footer bg-light">
                                    <p class="text-muted small mb-0 text-center">
                                        <i class="fas fa-lock me-1"></i>
                                        Your payment is secured and encrypted
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Handle iframe messages for payment completion -->
        <script>
            // Listen for messages from payment iframe (if payment gateway supports it)
            window.addEventListener('message', function(event) {
                // Verify origin for security
                if (event.origin !== window.location.origin) {
                    return;
                }
                
                // Handle payment success/failure messages
                if (event.data && event.data.type === 'payment_complete') {
                    if (event.data.success) {
                        window.location.href = event.data.redirect_url || '{{ route("home") }}';
                    }
                }
            });
            
            // Auto-resize iframe if needed
            function resizeIframe() {
                const iframe = document.getElementById('payment-iframe');
                if (iframe && iframe.contentWindow) {
                    try {
                        const height = iframe.contentWindow.document.body.scrollHeight;
                        if (height > 0) {
                            iframe.style.height = height + 'px';
                        }
                    } catch (e) {
                        // Cross-origin restrictions - ignore
                    }
                }
            }
            
            // Try to resize periodically
            setInterval(resizeIframe, 1000);
        </script>
    @elseif($paymentUrl)
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <p>Redirecting to payment gateway...</p>
                    <p class="text-muted">If you are not redirected automatically, <a href="{{ $paymentUrl }}">click here</a>.</p>
                </div>
            </div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = "{{ $paymentUrl }}";
            }, 2000);
        </script>
    @else
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Initializing payment...</p>
                </div>
            </div>
        </div>
    @endif
</div>
